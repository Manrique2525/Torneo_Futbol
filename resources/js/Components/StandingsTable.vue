<script setup>
defineProps({
    standings: { type: Array, required: true },
    puedeRecalcular: { type: Boolean, default: false },
});

const getPosicionClass = (posicion, total) => {
    if (posicion === 1) return 'text-amber-600 bg-amber-500/10';
    if (posicion <= 4 && total >= 6) return 'text-emerald-600 bg-emerald-500/10';
    if (posicion > total - 2 && total >= 6) return 'text-red-600 bg-red-500/10';
    return 'text-slate-600 bg-slate-500/10';
};

const getDgClass = (dg) => {
    if (dg > 0) return 'text-emerald-600';
    if (dg < 0) return 'text-red-600';
    return 'text-slate-400';
};

const getShieldStyle = (shield) => ({
    backgroundImage: shield ? `url(/storage/${shield})` : 'none',
});
</script>

<template>
    <div class="overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">#</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Equipo</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PJ</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PG</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PE</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PP</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">GF</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">GC</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">DG</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">FP</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Pts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                <tr
                    v-for="s in standings"
                    :key="s.id"
                    class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                >
                    <td class="p-4 text-center">
                        <span
                            class="inline-flex items-center justify-center h-7 w-7 rounded-lg text-[10px] font-black"
                            :class="getPosicionClass(s.posicion, standings.length)"
                        >
                            {{ s.posicion }}
                        </span>
                    </td>

                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-white/5 bg-cover bg-center border border-slate-200 dark:border-slate-700 shrink-0"
                                :style="getShieldStyle(s.equipo?.shield)"
                            >
                                <div v-if="!s.equipo?.shield" class="h-full w-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm text-slate-300 dark:text-slate-600">shield</span>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ s.equipo?.nombre ?? '—' }}
                            </span>
                        </div>
                    </td>

                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.pj }}</td>
                    <td class="p-4 text-center text-sm font-medium text-emerald-600">{{ s.pg }}</td>
                    <td class="p-4 text-center text-sm font-medium text-amber-600">{{ s.pe }}</td>
                    <td class="p-4 text-center text-sm font-medium text-red-600">{{ s.pp }}</td>
                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.gf }}</td>
                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.gc }}</td>
                    <td class="p-4 text-center text-sm font-black" :class="getDgClass(s.dg)">
                        {{ s.dg > 0 ? '+' : '' }}{{ s.dg }}
                    </td>
                    <td class="p-4 text-center text-sm font-medium text-primary">{{ s.fair_play }}</td>
                    <td class="p-4 text-center">
                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ s.pts }}</span>
                    </td>
                </tr>

                <tr v-if="!standings?.length">
                    <td colspan="11" class="text-center py-10 text-slate-400">
                        No hay datos de posiciones disponibles
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
