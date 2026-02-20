<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: Boolean,
    match: Object
});

const emit = defineEmits(['close', 'save']);

const homeScore = ref(props.match?.home_score || 0);
const awayScore = ref(props.match?.away_score || 0);

const save = () => {
    emit('save', {
        home_score: homeScore.value,
        away_score: awayScore.value
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>

        <div class="relative w-full max-w-lg overflow-hidden rounded-[3rem] bg-white shadow-2xl dark:bg-surface-dark border border-slate-200 dark:border-slate-800">

            <div class="p-8">
                <h3 class="text-center text-[11px] font-black uppercase tracking-[0.3em] text-primary mb-8">
                    Actualizar Marcador
                </h3>

                <div class="flex items-center justify-between gap-8">
                    <div class="flex flex-1 flex-col items-center gap-4">
                        <div class="h-16 w-16 rounded-2xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-xl font-black text-primary">
                            {{ match.home_team.substring(0,2) }}
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="homeScore > 0 && homeScore--" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-white/5 text-slate-500">-</button>
                            <span class="text-5xl font-black text-slate-900 dark:text-white tabular-nums">{{ homeScore }}</span>
                            <button @click="homeScore++" class="h-8 w-8 rounded-full border border-primary text-primary flex items-center justify-center hover:bg-primary/10">+</button>
                        </div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 text-center">{{ match.home_team }}</span>
                    </div>

                    <div class="text-xs font-black text-slate-300">VS</div>

                    <div class="flex flex-1 flex-col items-center gap-4">
                        <div class="h-16 w-16 rounded-2xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-xl font-black text-primary">
                            {{ match.away_team.substring(0,2) }}
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="awayScore > 0 && awayScore--" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-white/5 text-slate-500">-</button>
                            <span class="text-5xl font-black text-slate-900 dark:text-white tabular-nums">{{ awayScore }}</span>
                            <button @click="awayScore++" class="h-8 w-8 rounded-full border border-primary text-primary flex items-center justify-center hover:bg-primary/10">+</button>
                        </div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 text-center">{{ match.away_team }}</span>
                    </div>
                </div>

                <div class="mt-12 flex flex-col gap-3">
                    <PrimaryButton class="w-full justify-center py-4" @click="save">
                        Confirmar Resultado
                    </PrimaryButton>
                    <button @click="$emit('close')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
