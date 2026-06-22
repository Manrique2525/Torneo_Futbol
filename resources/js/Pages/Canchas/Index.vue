<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useCan } from '@/Shared/Composables/useCan.js';
import GoogleMapPicker from '@/Components/GoogleMapPicker.vue';

const props = defineProps({
    canchas: Object,
    constantes: Object,
    filters: Object,
    flash: Object,
});

const { hasRole } = useCan();

const constantes = props.constantes || {};

const searchQuery = ref(props.filters?.search || '');
const filterTipo = ref(props.filters?.tipo || 'todos');
const filterEstado = ref(props.filters?.estado || 'todos');

const tipoOptions = [
    { label: 'Todos los Tipos', value: 'todos' },
    ...Object.entries(constantes.tipos_cancha || {}).map(([key, label]) => ({
        label,
        value: key,
    })),
];

const estadoOptions = [
    { label: 'Todos los Estados', value: 'todos' },
    ...Object.entries(constantes.estados_cancha || {}).map(([key, label]) => ({
        label,
        value: key,
    })),
];

const debounce = (fn, delay) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
};

const search = debounce(() => {
    router.get(route('canchas.index'), {
        search: searchQuery.value,
        tipo: filterTipo.value === 'todos' ? undefined : filterTipo.value,
        estado: filterEstado.value === 'todos' ? undefined : filterEstado.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([searchQuery, filterTipo, filterEstado], search);

const confirmModal = ref({ show: false });

const triggerDelete = (cancha) => {
    confirmModal.value = {
        show: true,
        title: 'Eliminar Cancha',
        message: `¿Eliminar ${cancha.nombre}?`,
        action: () => router.delete(route('canchas.destroy', cancha.id), { onFinish: closeConfirm }),
    };
};

const closeConfirm = () => (confirmModal.value.show = false);

const getTipoBadge = (tipo) => {
    const colors = {
        'futbol-11': 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        'futbol-7': 'bg-green-500/10 text-green-600 border-green-500/20',
        'futbol-5': 'bg-amber-500/10 text-amber-600 border-amber-500/20',
        'futbol-sala': 'bg-purple-500/10 text-purple-600 border-purple-500/20',
    };
    return colors[tipo] || 'bg-slate-100 text-slate-500 border-slate-200';
};

const getEstadoBadge = (estado) => {
    const colors = {
        activo: 'bg-green-500/10 text-green-600 border-green-500/20',
        inactivo: 'bg-slate-500/10 text-slate-600 border-slate-500/20',
        mantenimiento: 'bg-amber-500/10 text-amber-600 border-amber-500/20',
    };
    return colors[estado] || 'bg-slate-100 text-slate-500 border-slate-200';
};

const canchaModal = ref(null);

const abrirModal = (c) => {
    canchaModal.value = c;
};

const cerrarModal = () => {
    canchaModal.value = null;
};

const truncate = (text, max) => {
    if (!text) return '';
    return text.length > max ? text.substring(0, max) + '...' : text;
};
</script>

<template>
<Head title="Canchas" />

<AuthenticatedLayout>
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Gestión de <span class="text-primary">Canchas</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ canchas?.total || 0 }} registros encontrados
            </p>
        </div>

        <Link
            v-if="!hasRole('delegate')"
            :href="route('canchas.create')"
            class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-2">stadium</span>
            Nueva Cancha
        </Link>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">

        <div class="md:col-span-2 relative">
            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                <span class="material-symbols-outlined text-xl">search</span>
            </span>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar cancha..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all"
            >
        </div>

        <div>
            <VSelectCustom
                v-model="filterTipo"
                :options="tipoOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Seleccionar tipo..."
            />
        </div>

        <div>
            <VSelectCustom
                v-model="filterEstado"
                :options="estadoOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Estado..."
            />
        </div>
    </div>

    <!-- FLASH -->
    <div
        v-if="flash?.success"
        class="bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
    >
        <span class="material-symbols-outlined text-green-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>

    <!-- TABLA -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Cancha</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Dirección</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Tipo</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Capacidad</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                        <th class="p-6"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <tr
                        v-for="c in canchas?.data || []"
                        :key="c.id"
                        @click="abrirModal(c)"
                        class="cursor-pointer group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                    >
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                    <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase">
                                        {{ c.nombre.substring(0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ c.nombre }}
                                    </span>
                                    <div v-if="c.latitud && c.longitud" class="flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined !text-[14px] text-slate-400">location_on</span>
                                        <span class="text-[10px] text-slate-400">
                                            {{ Number(c.latitud).toFixed(4) }}, {{ Number(c.longitud).toFixed(4) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="p-6">
                            <span v-if="c.direccion" class="text-sm text-slate-600 dark:text-slate-400">
                                {{ truncate(c.direccion, 40) }}
                            </span>
                            <span v-else class="text-xs text-slate-400">Sin dirección</span>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getTipoBadge(c.tipo)]">
                                {{ constantes.tipos_cancha?.[c.tipo] || c.tipo }}
                            </span>
                        </td>

                        <td class="p-6 text-center">
                            <span v-if="c.capacidad" class="text-sm font-bold text-primary">
                                {{ c.capacidad }} pers.
                            </span>
                            <span v-else class="text-xs text-slate-400">—</span>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getEstadoBadge(c.estado)]">
                                {{ constantes.estados_cancha?.[c.estado] || c.estado }}
                            </span>
                        </td>

                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                <Link
                                    v-if="!hasRole('delegate')"
                                    :href="route('canchas.edit', c.id)"
                                    class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">open_in_new</span>
                                </Link>

                                <button
                                    v-if="!hasRole('delegate')"
                                    @click="triggerDelete(c)"
                                    class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!canchas?.data?.length">
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            No hay canchas registradas
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Mostrando {{ canchas.from || 0 }} - {{ canchas.to || 0 }} de {{ canchas.total }} registros
                </span>
                <Pagination :links="canchas.links" />
            </div>
        </div>
    </div>

</div>

<!-- MODAL -->
<Modal :show="confirmModal.show" maxWidth="md" @close="closeConfirm">
    <div class="p-8">
        <h3 class="text-xl font-black uppercase tracking-tighter mb-4">
            {{ confirmModal.title }}
        </h3>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            {{ confirmModal.message }}
        </p>
        <div class="flex flex-col gap-3">
            <button
                @click="confirmModal.action"
                class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all"
            >
                Confirmar
            </button>
            <button
                @click="closeConfirm"
                class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400"
            >
                Cancelar
            </button>
        </div>
    </div>
</Modal>

<!-- Modal ubicación -->
<Teleport to="body">
    <div
        v-if="canchaModal"
        class="fixed inset-0 z-50 flex items-start justify-center pt-10 md:pt-20"
    >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="cerrarModal"></div>
        <div class="relative bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto mx-4 z-10">
            <!-- Header -->
            <div class="sticky top-0 bg-white dark:bg-[#1A2C26] z-10 p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                        <span class="material-symbols-outlined text-xl">stadium</span>
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                            {{ canchaModal.nombre }}
                        </h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            {{ constantes.tipos_cancha?.[canchaModal.tipo] || canchaModal.tipo }}
                            —
                            {{ constantes.estados_cancha?.[canchaModal.estado] || canchaModal.estado }}
                        </p>
                    </div>
                </div>
                <button
                    @click="cerrarModal"
                    class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-white/10 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-white/20 transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-5">
                <!-- Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Dirección</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            {{ canchaModal.direccion || 'Sin dirección' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Capacidad</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            {{ canchaModal.capacidad ? canchaModal.capacidad + ' personas' : '—' }}
                        </p>
                    </div>
                    <div v-if="canchaModal.latitud && canchaModal.longitud">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Latitud</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ canchaModal.latitud }}</p>
                    </div>
                    <div v-if="canchaModal.latitud && canchaModal.longitud">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Longitud</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ canchaModal.longitud }}</p>
                    </div>
                </div>

                <!-- Map -->
                <div v-if="canchaModal.latitud && canchaModal.longitud">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Ubicación</p>
                    <GoogleMapPicker
                        :model-value="{ lat: canchaModal.latitud, lng: canchaModal.longitud }"
                        :disabled="true"
                        height="300px"
                    />
                </div>
                <div v-else class="bg-slate-50 dark:bg-white/5 rounded-2xl p-6 text-center">
                    <span class="material-symbols-outlined text-3xl text-slate-300">map</span>
                    <p class="text-sm text-slate-400 mt-2">Esta cancha no tiene ubicación registrada</p>
                </div>
            </div>
        </div>
    </div>
</Teleport>

</AuthenticatedLayout>
</template>
