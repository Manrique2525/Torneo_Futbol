<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AgendaItem from '@/Components/AgendaItem.vue';
import ScoreModal from '@/Components/ScoreModal.vue';
import TeamStatsPanel from '@/Components/TeamStatsPanel.vue';
import Toast from '@/Components/Toast.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

// --- Estado de Datos ---
const agenda = ref([
    {
        date: 'Lunes, 24 de Octubre',
        matches: [
            { id: 1, time: '18:00', home_team: 'Lions FC', away_team: 'Rangers', field: 'Cancha Central', home_score: 2, away_score: 1 },
            { id: 2, time: '20:00', home_team: 'Sharks SC', away_team: 'United FC', field: 'Cancha 2', home_score: 0, away_score: 0 },
        ]
    }
]);

// --- Estado de UI ---
const isModalOpen = ref(false);
const isPanelOpen = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

const selectedMatch = ref(null);
const selectedTeam = ref(null);

// --- Lógica ---
const openScoreModal = (match) => {
    selectedMatch.value = { ...match };
    isModalOpen.value = true;
};

const openTeamStats = (teamName) => {
    selectedTeam.value = { name: teamName };
    isPanelOpen.value = true;
};

const handleSaveScore = (scores) => {
    // Simulación de guardado
    toastMessage.value = "Marcador actualizado con éxito";
    showToast.value = true;
    isModalOpen.value = false;

    // Actualizar visualmente la agenda
    const day = agenda.value[0];
    const match = day.matches.find(m => m.id === selectedMatch.value.id);
    match.home_score = scores.home_score;
    match.away_score = scores.away_score;
};
</script>

<template>
    <Head title="Calendario y Resultados" />

    <AuthenticatedLayout>
        <div class="fixed right-6 top-24 z-[200] flex flex-col gap-4">
            <Toast v-if="showToast" :message="toastMessage" @close="showToast = false" />
        </div>

        <div class="flex flex-col gap-8">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">Calendario</h2>
                <div class="flex gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 italic">Haz clic en un partido para editar o en un equipo para ver stats</span>
                </div>
            </div>

            <div class="space-y-10">
                <div v-for="day in agenda" :key="day.date">
                    <div class="sticky top-0 z-10 mb-4 flex items-center gap-4 bg-slate-50/90 py-3 backdrop-blur-md dark:bg-surface-dark/90">
                        <span class="text-[11px] font-black uppercase tracking-[0.3em] text-primary">{{ day.date }}</span>
                        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
                    </div>

                    <div class="space-y-3">
                        <div v-for="match in day.matches" :key="match.id" class="relative">
                            <AgendaItem :match="match" @click="openScoreModal(match)" class="cursor-pointer" />

                            <button @click.stop="openTeamStats(match.home_team)" class="absolute left-[25%] top-1/2 h-8 w-24 -translate-y-1/2 rounded hover:bg-primary/5"></button>
                            <button @click.stop="openTeamStats(match.away_team)" class="absolute right-[25%] top-1/2 h-8 w-24 -translate-y-1/2 rounded hover:bg-primary/5"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ScoreModal
            v-if="isModalOpen"
            :show="isModalOpen"
            :match="selectedMatch"
            @close="isModalOpen = false"
            @save="handleSaveScore"
        />

        <TeamStatsPanel
            :show="isPanelOpen"
            :team="selectedTeam"
            @close="isPanelOpen = false"
        />
    </AuthenticatedLayout>
</template>
