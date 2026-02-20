<script setup>
import ScoreBadge from '@/Components/ScoreBadge.vue';

defineProps({
    homeTeam: Object,
    awayTeam: Object,
    status: String, // 'Live', 'Scheduled', 'Finished'
    time: String,
    tournament: String
});
</script>

<template>
    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-1 shadow-xl transition-all duration-500 hover:shadow-2xl dark:border-slate-800 dark:bg-surface-dark group">
        <div v-if="status === 'Live'" class="absolute top-4 left-1/2 -translate-x-1/2 z-10">
            <span class="flex items-center gap-1.5 rounded-full bg-red-500 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-white animate-pulse">
                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                En Vivo
            </span>
        </div>

        <div class="px-8 py-10">
            <div class="flex items-center justify-between gap-4">

                <div class="flex flex-1 flex-col items-center gap-4 transition-transform duration-300 group-hover:-translate-y-1">
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-full bg-primary/5 blur-xl group-hover:bg-primary/10 transition-colors"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-slate-100 bg-slate-50 text-2xl font-black text-primary shadow-sm dark:border-slate-700 dark:bg-white/5">
                            {{ homeTeam.name.substring(0, 2).toUpperCase() }}
                        </div>
                    </div>
                    <h4 class="text-center text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white">
                        {{ homeTeam.name }}
                    </h4>
                </div>

                <div class="flex flex-col items-center">
                    <div class="flex items-center gap-4">
                        <span class="text-5xl font-black tabular-nums tracking-tighter text-slate-900 dark:text-white">
                            {{ homeTeam.score }}
                        </span>
                        <div class="h-1 w-4 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <span class="text-5xl font-black tabular-nums tracking-tighter text-slate-900 dark:text-white">
                            {{ awayTeam.score }}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-col items-center gap-1">
                        <span class="text-[11px] font-black uppercase tracking-[0.2em] text-primary">{{ time }}</span>
                        <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">{{ tournament }}</span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col items-center gap-4 transition-transform duration-300 group-hover:-translate-y-1">
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-full bg-primary/5 blur-xl group-hover:bg-primary/10 transition-colors"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-slate-100 bg-slate-50 text-2xl font-black text-primary shadow-sm dark:border-slate-700 dark:bg-white/5">
                            {{ awayTeam.name.substring(0, 2).toUpperCase() }}
                        </div>
                    </div>
                    <h4 class="text-center text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white">
                        {{ awayTeam.name }}
                    </h4>
                </div>

            </div>
        </div>

        <div class="bg-slate-50/50 px-6 py-4 dark:bg-black/20 border-t border-slate-100 dark:border-slate-800/50 flex justify-center gap-4">
            <button class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined !text-lg">analytics</span>
                Estadísticas
            </button>
            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
            <button class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined !text-lg">sports_soccer</span>
                Alineaciones
            </button>
        </div>
    </div>
</template>
