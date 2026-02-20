<script setup>
defineProps({
    matches: { type: Array, required: true }
});

const getStatusClasses = (status) => {
    const statuses = {
        'Live': 'bg-red-500/10 text-red-600 border-red-500/20 dark:bg-red-500/20 dark:text-red-400',
        'Finished': 'bg-primary/10 text-primary border-primary/20',
        'Scheduled': 'bg-blue-500/10 text-blue-600 border-blue-500/20 dark:bg-blue-500/20 dark:text-blue-400'
    };
    return statuses[status] || 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-surface-dark">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50/50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Fecha</th>
                        <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Local</th>
                        <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Visitante</th>
                        <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Estado</th>
                        <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Score</th>
                        <th class="px-6 py-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="match in matches" :key="match.id" class="group hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ match.date }}</span>
                            <span class="block text-[10px] font-medium text-slate-400 uppercase tracking-tighter">{{ match.time }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs font-black text-primary">
                                    {{ match.home_team.name.substring(0,2).toUpperCase() }}
                                </div>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ match.home_team.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs font-black text-primary">
                                    {{ match.away_team.name.substring(0,2).toUpperCase() }}
                                </div>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ match.away_team.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="['inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border', getStatusClasses(match.status)]">
                                <span v-if="match.status === 'Live'" class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </span>
                                {{ match.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-lg font-black text-slate-900 dark:text-white">
                                {{ match.status === 'Scheduled' ? '--' : `${match.home_score} - ${match.away_score}` }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="h-8 w-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                                <span class="material-symbols-outlined !text-lg">more_horiz</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
