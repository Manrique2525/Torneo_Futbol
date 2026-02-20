<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TeamCard from '@/Components/TeamCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';

const search = ref('');

// Datos de ejemplo
const teams = ref([
    { id: 1, name: 'Lions FC', category: 'Primera División', players_count: 22, rank: 1 },
    { id: 2, name: 'Tigers United', category: 'Primera División', players_count: 18, rank: 4 },
    { id: 3, name: 'Eagle Pro', category: 'Segunda División', players_count: 20, rank: 2 },
    { id: 4, name: 'Sharks SC', category: 'Primera División', players_count: 25, rank: 10 },
    { id: 5, name: 'Rangers', category: 'Segunda División', players_count: 19, rank: 3 },
    { id: 6, name: 'United FC', category: 'Primera División', players_count: 21, rank: 7 },
]);

const filteredTeams = computed(() => {
    return teams.value.filter(team =>
        team.name.toLowerCase().includes(search.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Equipos" />

    <AuthenticatedLayout>
        <div class="flex flex-col gap-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                        Equipos Registrados
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Administra los clubes y sus plantillas de jugadores.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar equipo..."
                            class="w-full rounded-xl border-none bg-slate-100 pl-10 py-2.5 text-xs font-bold uppercase tracking-widest focus:ring-2 focus:ring-primary dark:bg-white/5 dark:text-white"
                        />
                    </div>
                    <PrimaryButton>
                        <span class="material-symbols-outlined">add</span>
                        Nuevo Equipo
                    </PrimaryButton>
                </div>
            </div>

            <div v-if="filteredTeams.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <TeamCard
                    v-for="team in filteredTeams"
                    :key="team.id"
                    :team="team"
                />
            </div>

            <div v-else class="py-20 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-200 dark:text-slate-800">search_off</span>
                <p class="mt-4 font-black uppercase tracking-widest text-slate-400">No se encontraron equipos</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
