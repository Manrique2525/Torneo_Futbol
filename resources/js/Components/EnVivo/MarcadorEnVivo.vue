<script setup>
const props = defineProps({
    partido: Object,
    expulsadosLocal: Array,
    expulsadosVisitante: Array,
});

const equipoLocal = props.partido?.equipo_local;
const equipoVisitante = props.partido?.equipo_visitante;

const getShieldStyle = (shield) => ({
    backgroundImage: shield ? `url(/storage/${shield})` : 'none',
});
</script>

<template>
    <div class="flex items-center justify-center gap-3 sm:gap-6 md:gap-10 py-4 md:py-6">
        <!-- Local -->
        <div class="flex flex-col items-center gap-3 text-center w-24 sm:w-32">
            <div
                class="h-12 w-12 sm:h-16 sm:w-16 md:h-20 md:w-20 rounded-2xl bg-slate-100 dark:bg-white/5 bg-cover bg-center border-2 border-slate-200 dark:border-slate-700"
                :style="getShieldStyle(equipoLocal?.shield)"
            >
                <div v-if="!equipoLocal?.shield" class="h-full w-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-slate-300 dark:text-slate-600">shield</span>
                </div>
            </div>
            <span class="text-sm font-black uppercase tracking-tight text-slate-800 dark:text-white leading-tight">
                {{ equipoLocal?.nombre ?? 'Local' }}
            </span>
        </div>

        <!-- Marcador -->
        <div class="flex flex-col items-center gap-1">
            <div class="flex items-center gap-3 md:gap-5">
                <span class="text-3xl sm:text-4xl md:text-6xl font-black text-slate-900 dark:text-white tabular-nums">
                    {{ partido?.goles_local ?? 0 }}
                </span>
                <span class="text-lg sm:text-xl md:text-3xl font-black text-slate-300 dark:text-slate-600">—</span>
                <span class="text-3xl sm:text-4xl md:text-6xl font-black text-slate-900 dark:text-white tabular-nums">
                    {{ partido?.goles_visitante ?? 0 }}
                </span>
            </div>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                {{ partido?.estado === 'en_juego' ? `Mitad ${partido?.mitad}` : partido?.estado }}
            </span>
        </div>

        <!-- Visitante -->
        <div class="flex flex-col items-center gap-3 text-center w-24 sm:w-32">
            <div
                class="h-12 w-12 sm:h-16 sm:w-16 md:h-20 md:w-20 rounded-2xl bg-slate-100 dark:bg-white/5 bg-cover bg-center border-2 border-slate-200 dark:border-slate-700"
                :style="getShieldStyle(equipoVisitante?.shield)"
            >
                <div v-if="!equipoVisitante?.shield" class="h-full w-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-slate-300 dark:text-slate-600">shield</span>
                </div>
            </div>
            <span class="text-sm font-black uppercase tracking-tight text-slate-800 dark:text-white leading-tight">
                {{ equipoVisitante?.nombre ?? 'Visitante' }}
            </span>
        </div>
    </div>
</template>
