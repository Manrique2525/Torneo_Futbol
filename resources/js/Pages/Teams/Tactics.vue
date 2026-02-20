<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SoccerField from '@/Components/SoccerField.vue';
import FieldPlayer from '@/Components/FieldPlayer.vue';
import { ref } from 'vue';

const team = ref({
    name: 'Lions FC',
    players: [
        { name: 'Thibaut C.', number: 1, rating: 89, pos: 'top-[85%] left-[43%]' },
        { name: 'Eder M.', number: 3, rating: 84, pos: 'top-[70%] left-[20%]' },
        { name: 'David A.', number: 4, rating: 85, pos: 'top-[70%] left-[65%]' },
        { name: 'Luka M.', number: 10, rating: 88, pos: 'top-[45%] left-[43%]' },
        { name: 'Vini Jr.', number: 7, rating: 89, pos: 'top-[15%] left-[10%]' },
        { name: 'Benzema', number: 9, rating: 91, pos: 'top-[10%] left-[43%]' },
        { name: 'Rodrygo', number: 11, rating: 83, pos: 'top-[15%] left-[75%]' },
    ]
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex flex-col lg:flex-row gap-12 items-start">

            <div class="w-full lg:w-2/3 flex justify-center">
                <SoccerField>
                    <FieldPlayer
                        v-for="p in team.players"
                        :key="p.number"
                        :player="p"
                        :position-class="p.pos"
                    />
                </SoccerField>
            </div>

            <div class="w-full lg:w-1/3 space-y-6">
                <div class="rounded-[2.5rem] bg-white p-8 shadow-xl dark:bg-surface-dark border border-slate-200 dark:border-slate-800">
                    <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                        Ajustes Tácticos
                    </h3>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Formación</label>
                        <select class="w-full rounded-xl border-none bg-slate-100 py-3 text-xs font-bold dark:bg-white/5 dark:text-white">
                            <option>4-3-3 (Ofensiva)</option>
                            <option>4-4-2 (Clásica)</option>
                            <option>5-3-2 (Defensiva)</option>
                        </select>
                    </div>

                    <div class="mt-8 p-4 rounded-2xl bg-primary/10 border border-primary/20">
                        <p class="text-[10px] font-bold text-primary uppercase leading-relaxed">
                            💡 Tip: Puedes arrastrar a los jugadores para ajustar su posición exacta en el campo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
