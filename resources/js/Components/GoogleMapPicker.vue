<script setup>
import { ref, onMounted, watch, shallowRef, nextTick } from 'vue';
import L from 'leaflet';

// Fix Leaflet default icon paths for bundlers
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

const props = defineProps({
    modelValue: { type: Object, default: null },
    disabled: { type: Boolean, default: false },
    height: { type: String, default: '400px' },
});

const emit = defineEmits(['update:modelValue', 'address-update']);

const mapDiv = ref(null);
const searchInput = ref(null);
const searchResults = ref([]);
const searchOpen = ref(false);
const map = shallowRef(null);
const marker = shallowRef(null);
const cargando = ref(true);
const error = ref(null);

const defaultCenter = { lat: 19.4326, lng: -99.1332 };

const getCenter = () => {
    if (props.modelValue?.lat && props.modelValue?.lng) {
        return { lat: Number(props.modelValue.lat), lng: Number(props.modelValue.lng) };
    }
    return defaultCenter;
};

let searchTimeout = null;

const onSearchInput = () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value?.value?.trim();
    if (!q || q.length < 3) {
        searchResults.value = [];
        searchOpen.value = false;
        return;
    }
    searchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=mx`
            );
            const data = await res.json();
            searchResults.value = data.map((r) => ({
                label: r.display_name,
                lat: parseFloat(r.lat),
                lng: parseFloat(r.lon),
            }));
            searchOpen.value = searchResults.value.length > 0;
        } catch {
            searchResults.value = [];
            searchOpen.value = false;
        }
    }, 400);
};

const selectSearchResult = (result) => {
    searchOpen.value = false;
    searchInput.value.value = result.label;
    placeMarker(result.lat, result.lng);
    map.value.setView([result.lat, result.lng], 17);
    emit('update:modelValue', { lat: result.lat, lng: result.lng });
    emit('address-update', result.label);
};

const placeMarker = (lat, lng) => {
    if (marker.value) {
        marker.value.setLatLng([lat, lng]);
    } else {
        marker.value = L.marker([lat, lng], {
            draggable: !props.disabled,
        }).addTo(map.value);

        marker.value.on('dragend', () => {
            const pos = marker.value.getLatLng();
            emit('update:modelValue', { lat: pos.lat, lng: pos.lng });
            reverseGeocode(pos.lat, pos.lng);
        });
    }
};

const reverseGeocode = async (lat, lng) => {
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
        );
        const data = await res.json();
        if (data.display_name) {
            emit('address-update', data.display_name);
        }
    } catch {
        // silent fail
    }
};

onMounted(async () => {
    try {
        // Wait for DOM to be fully laid out
        await nextTick();

        const center = getCenter();

        const mapInstance = L.map(mapDiv.value, {
            center: [center.lat, center.lng],
            zoom: 15,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(mapInstance);

        map.value = mapInstance;

        if (center.lat && center.lng) {
            placeMarker(center.lat, center.lng);
        }

        mapInstance.on('click', (e) => {
            if (props.disabled) return;
            const { lat, lng } = e.latlng;
            placeMarker(lat, lng);
            mapInstance.setView([lat, lng], mapInstance.getZoom());
            emit('update:modelValue', { lat, lng });
            reverseGeocode(lat, lng);
        });

        // Ensure map renders correctly after layout settles
        setTimeout(() => mapInstance.invalidateSize(), 100);

        cargando.value = false;
    } catch (e) {
        error.value = 'Error al cargar el mapa: ' + e.message;
        cargando.value = false;
    }
});

watch(() => props.modelValue, (val) => {
    if (!val?.lat || !val?.lng || !map.value) return;
    const pos = [Number(val.lat), Number(val.lng)];
    if (marker.value) {
        marker.value.setLatLng(pos);
    } else {
        placeMarker(pos[0], pos[1]);
    }
    map.value.setView(pos, map.value.getZoom());
}, { deep: true });

watch(() => props.disabled, (val) => {
    if (marker.value) {
        marker.value.dragging?.[val ? 'disable' : 'enable']();
    }
});

// Close search on click outside
const onBlur = () => {
    setTimeout(() => { searchOpen.value = false; }, 200);
};
</script>

<template>
<div>
    <!-- Search box -->
    <div class="relative mb-3">
        <span class="absolute top-3 left-3 flex items-start text-slate-400 z-10">
            <span class="material-symbols-outlined text-lg">search</span>
        </span>
        <input
            ref="searchInput"
            type="text"
            placeholder="Buscar dirección o lugar..."
            :disabled="disabled"
            @input="onSearchInput"
            @blur="onBlur"
            @focus="searchOpen = searchResults.length > 0"
            class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-white/5 border border-transparent
                   focus:border-primary focus:ring-2 focus:ring-primary/20
                   rounded-xl text-sm text-slate-800 dark:text-white
                   placeholder:text-slate-400 transition-all outline-none"
        >
        <!-- Search results dropdown -->
        <div
            v-if="searchOpen"
            class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-[#1A2C26] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden"
        >
            <button
                v-for="r in searchResults"
                :key="r.label"
                @mousedown.prevent="selectSearchResult(r)"
                class="w-full text-left px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 border-b border-slate-50 dark:border-slate-800/50 last:border-0 transition-colors"
            >
                <span class="material-symbols-outlined !text-sm align-middle mr-2 text-primary">location_on</span>
                {{ r.label }}
            </button>
        </div>
    </div>

    <!-- Map wrapper with absolute overlays -->
    <div class="relative rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800" :style="{ height }">
        <!-- Loading overlay -->
        <div v-if="cargando"
            class="absolute inset-0 flex items-center justify-center bg-slate-50 dark:bg-white/5 z-20"
        >
            <div class="flex flex-col items-center gap-2">
                <span class="material-symbols-outlined animate-spin text-primary text-3xl">refresh</span>
                <span class="text-xs font-bold text-slate-500">Cargando mapa...</span>
            </div>
        </div>

        <!-- Error overlay -->
        <div v-else-if="error"
            class="absolute inset-0 flex items-center justify-center bg-red-500/10 z-20"
        >
            <p class="text-sm font-bold text-red-600 px-4 text-center">{{ error }}</p>
        </div>

        <!-- Map (always visible) -->
        <div
            ref="mapDiv"
            class="w-full h-full"
            :style="{ cursor: disabled ? 'default' : 'crosshair' }"
        ></div>
    </div>

    <p v-if="!cargando && !error" class="text-[10px] text-slate-400 mt-2">
        <span class="material-symbols-outlined !text-xs align-middle mr-0.5">touch_app</span>
        Haz clic en el mapa para colocar un pin o arrastra el pin existente para ajustar la ubicación.
    </p>
</div>
</template>
