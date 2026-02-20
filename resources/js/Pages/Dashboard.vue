<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MatchTable from '@/Components/MatchTable.vue';
import MetricCard from '@/Components/MetricCard.vue';
import MatchLiveCard from '@/Components/MatchLiveCard.vue';
import TabSelector from '@/Components/TabSelector.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head } from '@inertiajs/vue3';

// Estado para las pestañas
const currentTab = ref('Live');
const tabs = [
    { id: 'Live', label: 'En Vivo' },
    { id: 'Scheduled', label: 'Próximos' },
    { id: 'Finished', label: 'Finalizados' }
];

// Datos de ejemplo optimizados
const recentMatches = ref([
    {
        id: 1,
        date: 'Oct 24, 2023',
        time: '14:00 PM',
        tournament: 'Liga de Campeones',
        home_team: { name: 'Lions FC', score: 2 },
        away_team: { name: 'Tigers United', score: 1 },
        status: 'Live',
    },
    {
        id: 2,
        date: 'Oct 25, 2023',
        time: '10:00 AM',
        tournament: 'Torneo Apertura',
        home_team: { name: 'Eagle Pro', score: 1 },
        away_team: { name: 'Sharks SC', score: 0 },
        status: 'Finished',
    },
    {
        id: 3,
        date: 'Oct 26, 2023',
        time: '18:00 PM',
        tournament: 'Copa Local',
        home_team: { name: 'Rangers', score: 0 },
        away_team: { name: 'United FC', score: 0 },
        status: 'Scheduled',
    }
]);

// Lógica de filtrado para la tabla
const filteredMatches = computed(() => {
    return recentMatches.value.filter(match => match.status === currentTab.value);
});

// Partido destacado (el primero que esté en Live)
const featuredMatch = computed(() => {
    return recentMatches.value.find(m => m.status === 'Live');
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="flex flex-col gap-10 pb-12">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard title="Torneos Activos" value="3" icon="emoji_events" trend="1" />
                <MetricCard title="Equipos Totales" value="48" icon="groups" trend="10"
                    icon-color-class="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
                <MetricCard title="Partidos Hoy" value="12" icon="schedule" trend="5"
                    icon-color-class="bg-orange-500/10 text-orange-600 dark:text-orange-400" />
                <MetricCard title="Rendimiento" value="85%" icon="stadium" trend="2" :trend-up="false"
                    icon-color-class="bg-purple-500/10 text-purple-600 dark:text-purple-400" />
            </div>

            <div v-if="featuredMatch" class="flex flex-col gap-4">
                <h3 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400 ml-2">
                    Destacado ahora
                </h3>
                <MatchLiveCard
                    :home-team="featuredMatch.home_team"
                    :away-team="featuredMatch.away_team"
                    :status="featuredMatch.status"
                    time="75'"
                    :tournament="featuredMatch.tournament"
                />
            </div>

            <div class="flex flex-col gap-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">
                            Calendario de Partidos
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Gestiona y monitorea los encuentros de la liga.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <TabSelector v-model="currentTab" :tabs="tabs" />
                        <PrimaryButton @click="router.visit(route('profile.edit'))">
                            <span class="material-symbols-outlined !text-sm">add</span>
                            Nuevo
                        </PrimaryButton>
                    </div>
                </div>

                <div v-if="filteredMatches.length > 0">
                    <MatchTable :matches="filteredMatches" />
                </div>

                <EmptyState
                    v-else
                    :title="`No hay partidos ${currentTab}`"
                    :description="`Actualmente no se encuentran encuentros en estado ${currentTab.toLowerCase()}.`"
                    icon="sports_score"
                    action-label="Programar Partido"
                />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
