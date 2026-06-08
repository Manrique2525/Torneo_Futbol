<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StandingsTable from '@/Components/StandingsTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useCan } from '@/Shared/Composables/useCan';

const props = defineProps({
    torneo: Object,
    grupos: Array,
    flash: Object,
});

const { can } = useCan();

const grupoActivo = ref(0);

const recalcular = () => {
    if (!confirm('¿Recalcular la tabla de posiciones?')) return;
    router.post(route('standings.recalcular', props.torneo.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
<Head :title="`Posiciones — ${torneo.nombre}`" />

<AuthenticatedLayout>
<div class="w-full space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <Link
                :href="route('torneos.index')"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary mb-2"
            >
                <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
                Torneos
            </Link>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Tabla de <span class="text-primary">Posiciones</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ torneo?.nombre }} — Liga
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                v-if="can('standings.recalculate')"
                @click="recalcular"
                class="px-4 py-2.5 rounded-xl bg-primary text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
            >
                <span class="material-symbols-outlined !text-sm mr-1">refresh</span>
                Recalcular
            </button>
        </div>
    </div>

    <!-- Flash -->
    <div v-if="flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>
    <div v-if="flash?.error" class="bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-red-600">error</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.error }}</span>
    </div>

    <!-- Selector de grupos -->
    <div v-if="grupos?.length > 1" class="flex flex-wrap gap-2">
        <button
            v-for="(grupo, idx) in grupos"
            :key="grupo.id ?? idx"
            @click="grupoActivo = idx"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="grupoActivo === idx
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            {{ grupo.nombre }}
        </button>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">leaderboard</span>
            </div>
            <div>
                <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    {{ grupos?.[grupoActivo]?.nombre ?? 'General' }}
                </h3>
                <p class="text-[11px] text-slate-400 font-medium">
                    {{ grupos?.[grupoActivo]?.standings?.length ?? 0 }} equipos
                </p>
            </div>
        </div>

        <StandingsTable
            :standings="grupos?.[grupoActivo]?.standings ?? []"
            :puede-recalcular="can('standings.recalculate')"
        />
    </div>

</div>
</AuthenticatedLayout>
</template>
