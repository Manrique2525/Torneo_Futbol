<script setup>
defineProps({
    team: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="group relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 dark:border-slate-800 dark:bg-surface-dark">
        <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-primary/5 transition-transform duration-700 group-hover:scale-150"></div>

        <div class="relative flex flex-col items-center">
            <div class="mb-5 flex h-24 w-24 items-center justify-center rounded-[2rem] bg-slate-100 text-3xl font-black text-primary shadow-inner dark:bg-white/5 dark:text-white">
                {{ team.name.substring(0, 2).toUpperCase() }}
            </div>

            <h3 class="text-lg font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                {{ team.name }}
            </h3>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">
                {{ team.category || 'Categoría Libre' }}
            </p>

            <div class="mt-6 flex w-full items-center justify-around border-t border-slate-100 pt-6 dark:border-slate-800/50">
                <div class="text-center">
                    <span class="block text-lg font-black text-slate-900 dark:text-white">{{ team.players_count }}</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Jugadores</span>
                </div>
                <div class="h-8 w-px bg-slate-100 dark:bg-slate-800"></div>
                <div class="text-center">
                    <span class="block text-lg font-black text-primary">{{ team.rank }}°</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Posición</span>
                </div>
            </div>

            <button class="mt-6 w-full rounded-xl bg-slate-900 py-3 text-[10px] font-black uppercase tracking-widest text-white opacity-0 transition-all duration-300 group-hover:opacity-100 dark:bg-primary">
                Ver Plantilla
            </button>
        </div>
    </div>
</template>
