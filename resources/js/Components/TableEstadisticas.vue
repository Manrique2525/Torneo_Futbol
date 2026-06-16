<script setup>
const props = defineProps({
    titulo: { type: String, required: true },
    icono: { type: String, default: 'bar_chart' },
    color: { type: String, default: 'primary' },
    columnas: { type: Array, required: true },
    datos: { type: Array, required: true },
    valorLabel: { type: String, default: 'Total' },
    valorCampo: { type: String, required: true },
});

const colorClasses = {
    primary: { bg: 'bg-primary', shadow: 'shadow-primary/30' },
    emerald: { bg: 'bg-emerald-500', shadow: 'shadow-emerald-500/30' },
    amber: { bg: 'bg-amber-500', shadow: 'shadow-amber-500/30' },
    red: { bg: 'bg-red-500', shadow: 'shadow-red-500/30' },
    slate: { bg: 'bg-slate-500', shadow: 'shadow-slate-500/30' },
};

const cc = colorClasses[props.color] ?? colorClasses.primary;
</script>

<template>
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div
                class="h-10 w-10 rounded-2xl flex items-center justify-center text-white shadow-md"
                :class="`${cc.bg} ${cc.shadow}`"
            >
                <span class="material-symbols-outlined text-xl">{{ icono }}</span>
            </div>
            <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">{{ titulo }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center w-12">#</th>
                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Jugador</th>
                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Equipo</th>
                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center w-20">{{ valorLabel }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <tr
                        v-for="(row, idx) in datos"
                        :key="idx"
                        class="hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                    >
                        <td class="p-4 text-center">
                            <span
                                class="inline-flex items-center justify-center h-7 w-7 rounded-lg text-[10px] font-black"
                                :class="idx === 0
                                    ? 'text-amber-600 bg-amber-500/10'
                                    : idx < 3
                                        ? 'text-slate-600 bg-slate-500/10'
                                        : 'text-slate-400 bg-transparent'"
                            >
                                {{ idx + 1 }}
                            </span>
                        </td>

                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-black shrink-0"
                                >
                                    {{ row.jugador_numero ?? '-' }}
                                </div>
                                <span class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                    {{ row.jugador_nombre }}
                                </span>
                            </div>
                        </td>

                        <td class="p-4">
                            <span class="text-sm font-medium text-slate-500">
                                {{ row.equipo_nombre }}
                            </span>
                        </td>

                        <td class="p-4 text-center">
                            <span
                                class="inline-flex items-center justify-center h-8 w-8 rounded-xl font-black text-sm"
                                :class="`${cc.bg}/10 text-slate-900 dark:text-white`"
                            >
                                {{ row[valorCampo] }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="!datos.length">
                        <td colspan="4" class="text-center py-10 text-slate-400">
                            Sin datos disponibles
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
