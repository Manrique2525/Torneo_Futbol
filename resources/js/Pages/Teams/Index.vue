<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    teams: Object,
    filters: Object,
    flash: Object,
    constantes: Object
});

const coloresEquipos = props.constantes?.colores_equipos || {};

// filtros
const searchQuery = ref(props.filters?.search || '');

// debounce
const debounce = (fn, delay) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
};

const search = debounce(() => {
    router.get(route('teams.index'), {
        search: searchQuery.value,
    }, { preserveState: true, replace: true });
}, 300);

watch(searchQuery, search);

// modal
const confirmModal = ref({ show: false });

const triggerDelete = (team) => {
    confirmModal.value = {
        show: true,
        title: 'Eliminar Equipo',
        message: `¿Eliminar el equipo "${team.name}"? Esta acción no se puede deshacer.`,
        action: () => router.delete(route('teams.destroy', team.id), { onFinish: closeConfirm })
    };
};

const closeConfirm = () => confirmModal.value.show = false;

// badges de estado
const getStatusBadge = (status) => {
    const colors = {
        active:     'bg-green-500/10 text-green-600 border-green-500/20',
        suspended:  'bg-red-500/10 text-red-600 border-red-500/20',
        inactive:   'bg-slate-500/10 text-slate-600 border-slate-500/20',
    };
    return colors[status] || 'bg-slate-100 text-slate-500 border-slate-200';
};

// texto legible del estado
const getStatusText = (status) => {
    const texts = {
        active: 'Activo',
        suspended: 'Suspendido',
        inactive: 'Inactivo',
    };
    return texts[status] || status;
};

// Función para obtener array de colores desde el string guardado
const getColorArray = (colorsString) => {
    if (!colorsString) return [];
    
    // Si tiene separador de coma
    if (colorsString.includes(',')) {
        return colorsString
            .split(',')
            .map(c => c.trim().toUpperCase())
            .filter(c => c && coloresEquipos[c]);
    }
    
    // Si tiene separador de slash
    if (colorsString.includes('/')) {
        return colorsString
            .split('/')
            .map(c => c.trim().toUpperCase())
            .filter(c => c && coloresEquipos[c]);
    }
    
    // Color único
    const color = colorsString.trim().toUpperCase();
    return coloresEquipos[color] ? [color] : [];
};

// Función para determinar si un color es claro
const isLightColor = (colorHex) => {
    if (!colorHex || colorHex === '#FFFFFF') return true;
    if (colorHex === '#FFFF00') return true; // Amarillo
    if (colorHex === '#FFC0CB') return true; // Rosado
    if (colorHex === '#C0C0C0') return true; // Plateado
    if (colorHex === '#FFD700') return true; // Dorado
    
    // Calcular luminosidad para hex
    if (colorHex.startsWith('#')) {
        const r = parseInt(colorHex.slice(1, 3), 16);
        const g = parseInt(colorHex.slice(3, 5), 16);
        const b = parseInt(colorHex.slice(5, 7), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness > 200;
    }
    return false;
};
</script>

<template>
<Head title="Equipos" />

<AuthenticatedLayout>
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Gestión de <span class="text-primary">Equipos</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ teams?.total || 0 }} registros encontrados
            </p>
        </div>

        <Link
            :href="route('teams.create')"
            class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-2">shield</span>
            Nuevo Equipo
        </Link>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">

        <div class="md:col-span-3 relative">
            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                <span class="material-symbols-outlined text-xl">search</span>
            </span>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar equipo por nombre..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all"
            >
        </div>

        <div class="flex items-center justify-center bg-primary/5 rounded-2xl border border-dashed border-primary/20">
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-primary">
                Filtros activos
            </span>
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
    
    <div
        v-if="flash?.error"
        class="bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
    >
        <span class="material-symbols-outlined text-red-600">error</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.error }}</span>
    </div>

    <!-- TABLA -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Equipo</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Delegado</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Contacto</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Colores</th>
                        <th class="p-6"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <tr
                        v-for="team in teams?.data || []"
                        :key="team.id"
                        class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                    >
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <!-- Escudo / Logo -->
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                    <div v-if="team.shield" class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center overflow-hidden">
                                        <img :src="'/storage/' + team.shield" :alt="team.name" class="h-full w-full object-cover rounded-[14px]">
                                    </div>
                                    <div v-else class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase text-lg">
                                        {{ team.name.substring(0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ team.name }}
                                    </span>
                                    <p class="text-xs text-slate-500">
                                        {{ team.email || 'Sin email registrado' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="p-6">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ team.delegado?.name || 'No asignado' }}
                                </span>
                                <span v-if="team.delegado?.email" class="text-xs text-slate-500">
                                    {{ team.delegado.email }}
                                </span>
                            </div>
                        </td>

                        <td class="p-6">
                            <div class="flex flex-col">
                                <span v-if="team.phone" class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined !text-sm">phone</span>
                                    {{ team.phone }}
                                </span>
                                <span v-else class="text-sm text-slate-400">Sin teléfono</span>
                            </div>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getStatusBadge(team.status)]">
                                {{ getStatusText(team.status) }}
                            </span>
                        </td>

                        <td class="p-6">
                            <div v-if="getColorArray(team.colors).length > 0" class="flex items-center justify-center gap-2 flex-wrap">
                                <div 
                                    v-for="colorName in getColorArray(team.colors)" 
                                    :key="colorName"
                                    class="relative group/color"
                                >
                                    <div 
                                        class="h-8 w-8 rounded-full border-2 shadow-md transition-all duration-200 hover:scale-110 hover:shadow-lg cursor-help"
                                        :class="isLightColor(coloresEquipos[colorName]) ? 'border-slate-300' : 'border-slate-600'"
                                        :style="{ backgroundColor: coloresEquipos[colorName] }"
                                    ></div>
                                    <!-- Tooltip con el nombre del color -->
                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-slate-800 text-white text-[10px] font-bold rounded opacity-0 group-hover/color:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                        {{ colorName }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center">
                                <span class="text-xs text-slate-400">No especificados</span>
                            </div>
                        </td>

                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('teams.edit', team.id)"
                                    class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                                    :title="'Editar ' + team.name"
                                >
                                    <span class="material-symbols-outlined !text-lg">edit</span>
                                </Link>

                                <button
                                    @click="triggerDelete(team)"
                                    class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                    :title="'Eliminar ' + team.name"
                                >
                                    <span class="material-symbols-outlined !text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!teams?.data?.length">
                        <td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center gap-4">
                                <span class="material-symbols-outlined text-5xl text-slate-400">shield</span>
                                <span class="text-slate-400 font-medium">No hay equipos registrados</span>
                                <Link
                                    :href="route('teams.create')"
                                    class="text-primary text-sm font-bold uppercase tracking-wider hover:underline"
                                >
                                    Crear primer equipo
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Mostrando {{ teams.from || 0 }} - {{ teams.to || 0 }} de {{ teams.total }} registros
                </span>
                <Pagination :links="teams.links" />
            </div>
        </div>
    </div>

</div>

<!-- MODAL DE CONFIRMACIÓN -->
<Modal :show="confirmModal.show" maxWidth="md" @close="closeConfirm">
    <div class="p-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-red-500/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600">warning</span>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                {{ confirmModal.title }}
            </h3>
        </div>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            {{ confirmModal.message }}
        </p>
        <div class="flex flex-col gap-3">
            <button
                @click="confirmModal.action"
                class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all"
            >
                Eliminar
            </button>
            <button
                @click="closeConfirm"
                class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors"
            >
                Cancelar
            </button>
        </div>
    </div>
</Modal>

</AuthenticatedLayout>
</template>