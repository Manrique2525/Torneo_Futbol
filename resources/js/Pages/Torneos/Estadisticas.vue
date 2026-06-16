<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableEstadisticas from '@/Components/TableEstadisticas.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    torneo: Object,
    estadisticas: Object,
    flash: Object,
});

const tabActivo = ref('goleo');

const tabs = [
    { id: 'goleo', label: 'Goleo', icon: 'sports_soccer' },
    { id: 'asistencias', label: 'Asistencias', icon: 'assist_walker' },
    { id: 'tarjetas_amarillas', label: 'T. Amarillas', icon: 'style' },
    { id: 'tarjetas_rojas', label: 'T. Rojas', icon: 'style' },
    { id: 'faltas', label: 'Faltas', icon: 'gavel' },
];

const tabConfig = computed(() => {
    const configs = {
        goleo: { titulo: 'Tabla de Goleo', icono: 'sports_soccer', color: 'emerald', valorCampo: 'goles', valorLabel: 'Goles' },
        asistencias: { titulo: 'Asistencias', icono: 'assist_walker', color: 'primary', valorCampo: 'asistencias', valorLabel: 'Asist.' },
        tarjetas_amarillas: { titulo: 'Tarjetas Amarillas', icono: 'style', color: 'amber', valorCampo: 'total', valorLabel: 'TA' },
        tarjetas_rojas: { titulo: 'Tarjetas Rojas', icono: 'style', color: 'red', valorCampo: 'total', valorLabel: 'TR' },
        faltas: { titulo: 'Faltas', icono: 'gavel', color: 'slate', valorCampo: 'total', valorLabel: 'Faltas' },
    };
    return configs[tabActivo.value];
});

const tipoLabel = computed(() => {
    const labels = { liga: 'Liga', copa: 'Copa', relampago: 'Relámpago' };
    return labels[props.torneo?.tipo] ?? props.torneo?.tipo;
});
</script>

<template>
<Head :title="`Estadísticas — ${torneo.nombre}`" />

<AuthenticatedLayout>
<div class="w-full space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <Link
                :href="route('standings.index', torneo.id)"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary mb-2"
            >
                <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
                Posiciones
            </Link>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Estadísticas del <span class="text-primary">Torneo</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ torneo?.nombre }} — {{ tipoLabel }}
            </p>
        </div>
    </div>

    <!-- Flash -->
    <div v-if="flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2">
        <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="tabActivo = tab.id"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="tabActivo === tab.id
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            <span class="material-symbols-outlined !text-sm">{{ tab.icon }}</span>
            {{ tab.label }}
        </button>
    </div>

    <!-- Tabla activa -->
    <TableEstadisticas
        :key="tabActivo"
        :titulo="tabConfig.titulo"
        :icono="tabConfig.icono"
        :color="tabConfig.color"
        :datos="estadisticas[tabActivo] ?? []"
        :valor-campo="tabConfig.valorCampo"
        :valor-label="tabConfig.valorLabel"
    />

</div>
</AuthenticatedLayout>
</template>
