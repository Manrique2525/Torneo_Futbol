<script setup>
import { ref, onMounted, watch, shallowRef } from 'vue';
import { setOptions, importLibrary } from '@googlemaps/js-api-loader';

const props = defineProps({
    modelValue: { type: Object, default: null },
    disabled: { type: Boolean, default: false },
    height: { type: String, default: '400px' },
});

const emit = defineEmits(['update:modelValue', 'address-update']);

const mapDiv = ref(null);
const searchInput = ref(null);
const map = shallowRef(null);
const marker = shallowRef(null);
const autocomplete = shallowRef(null);
const cargando = ref(true);
const error = ref(null);

const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const defaultCenter = { lat: 19.4326, lng: -99.1332 };

const getCenter = () => {
    if (props.modelValue?.lat && props.modelValue?.lng) {
        return { lat: Number(props.modelValue.lat), lng: Number(props.modelValue.lng) };
    }
    return defaultCenter;
};

onMounted(async () => {
    if (!API_KEY) {
        error.value = 'API Key de Google Maps no configurada (VITE_GOOGLE_MAPS_API_KEY)';
        cargando.value = false;
        return;
    }

    try {
        setOptions({ key: API_KEY, version: 'weekly' });

        await Promise.all([
            importLibrary('maps'),
            importLibrary('places'),
            importLibrary('geocoding'),
        ]);

        const center = getCenter();

        const mapInstance = new google.maps.Map(mapDiv.value, {
            center,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            styles: [
                { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            ],
        });

        map.value = mapInstance;

        const markerInstance = new google.maps.Marker({
            map: mapInstance,
            position: center,
            draggable: !props.disabled,
            animation: google.maps.Animation.DROP,
        });

        marker.value = markerInstance;

        if (props.modelValue?.lat && props.modelValue?.lng) {
            markerInstance.setPosition(getCenter());
            mapInstance.setCenter(getCenter());
        }

        markerInstance.addListener('dragend', () => {
            const pos = markerInstance.getPosition();
            const lat = pos.lat();
            const lng = pos.lng();
            emit('update:modelValue', { lat, lng });
            reverseGeocode(lat, lng);
        });

        mapInstance.addListener('click', (e) => {
            if (props.disabled) return;
            const lat = e.latLng.lat();
            const lng = e.latLng.lng();
            markerInstance.setPosition(e.latLng);
            mapInstance.panTo(e.latLng);
            emit('update:modelValue', { lat, lng });
            reverseGeocode(lat, lng);
        });

        // Places Autocomplete
        if (searchInput.value) {
            const auto = new google.maps.places.Autocomplete(searchInput.value, {
                types: ['geocode', 'establishment'],
                componentRestrictions: { country: 'mx' },
            });
            autocomplete.value = auto;

            auto.addListener('place_changed', () => {
                const place = auto.getPlace();
                if (!place.geometry || !place.geometry.location) return;

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                markerInstance.setPosition(place.geometry.location);
                mapInstance.setCenter(place.geometry.location);
                mapInstance.setZoom(17);

                emit('update:modelValue', { lat, lng });
                emit('address-update', place.formatted_address || place.name || '');
            });
        }

        cargando.value = false;
    } catch (e) {
        error.value = 'Error al cargar Google Maps: ' + e.message;
        cargando.value = false;
    }
});

const reverseGeocode = async (lat, lng) => {
    try {
        const geocoder = new google.maps.Geocoder();
        const result = await geocoder.geocode({ location: { lat, lng } });
        if (result.results?.[0]?.formatted_address) {
            emit('address-update', result.results[0].formatted_address);
        }
    } catch {
        // silent fail for reverse geocode
    }
};

watch(() => props.modelValue, (val) => {
    if (!val?.lat || !val?.lng || !map.value || !marker.value) return;
    const pos = { lat: Number(val.lat), lng: Number(val.lng) };
    marker.value.setPosition(pos);
    map.value.setCenter(pos);
}, { deep: true });

watch(() => props.disabled, (val) => {
    if (marker.value) {
        marker.value.setDraggable(!val);
    }
});
</script>

<template>
<div>
    <!-- Search box -->
    <div v-show="!cargando && !error" class="relative mb-3">
        <span class="absolute top-3 left-3 flex items-start text-slate-400 z-10">
            <span class="material-symbols-outlined text-lg">search</span>
        </span>
        <input
            ref="searchInput"
            type="text"
            placeholder="Buscar dirección o lugar..."
            :disabled="disabled"
            class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-white/5 border border-transparent
                   focus:border-primary focus:ring-2 focus:ring-primary/20
                   rounded-xl text-sm text-slate-800 dark:text-white
                   placeholder:text-slate-400 transition-all outline-none"
        >
    </div>

    <!-- Loading -->
    <div v-if="cargando"
        class="flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-slate-800"
        :style="{ height }"
    >
        <div class="flex flex-col items-center gap-2">
            <span class="material-symbols-outlined animate-spin text-primary text-3xl">refresh</span>
            <span class="text-xs font-bold text-slate-500">Cargando mapa...</span>
        </div>
    </div>

    <!-- Error -->
    <div v-else-if="error"
        class="flex items-center justify-center rounded-2xl bg-red-500/10 border border-red-500/20"
        :style="{ height }"
    >
        <p class="text-sm font-bold text-red-600 px-4 text-center">{{ error }}</p>
    </div>

    <!-- Map -->
    <div
        v-show="!cargando && !error"
        ref="mapDiv"
        class="rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden"
        :style="{ height, cursor: disabled ? 'default' : 'crosshair' }"
    ></div>

    <p v-if="!cargando && !error" class="text-[10px] text-slate-400 mt-2">
        <span class="material-symbols-outlined !text-xs align-middle mr-0.5">touch_app</span>
        Haz clic en el mapa para colocar un pin o arrastra el pin existente para ajustar la ubicación.
    </p>
</div>
</template>
