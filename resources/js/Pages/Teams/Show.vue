<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlayerCard from '@/Components/PlayerCard.vue';
import { Head } from '@inertiajs/vue3';

const players = [
    {
        id: 1, name: 'K. Benzema', number: 9, rating: 91, position: 'DC',
        team_name: 'Lions FC',
        stats: { PAC: 87, SHO: 92, PAS: 83, DRI: 88, DEF: 39, PHY: 78 }
    },
    {
        id: 2, name: 'L. Modric', number: 10, rating: 88, position: 'MC',
        team_name: 'Lions FC',
        stats: { PAC: 73, SHO: 76, PAS: 89, DRI: 87, DEF: 72, PHY: 66 }
    },
    {
        id: 3, name: 'V. Junior', number: 7, rating: 89, position: 'EI',
        team_name: 'Lions FC',
        stats: { PAC: 95, SHO: 82, PAS: 77, DRI: 90, DEF: 29, PHY: 68 }
    }
];
</script>

<template>
    <Head title="Plantilla" />

    <AuthenticatedLayout>
        <div class="flex flex-col gap-10">
            <div class="rounded-[3rem] bg-slate-900 p-10 text-white dark:bg-surface-dark">
                <div class="flex items-center gap-8">
                    <div class="h-24 w-24 rounded-3xl bg-primary flex items-center justify-center text-4xl font-black">
                        LI
                    </div>
                    <div>
                        <h2 class="text-4xl font-black uppercase tracking-tighter">Lions FC</h2>
                        <div class="mt-2 flex gap-4">
                            <span class="text-xs font-bold uppercase tracking-widest text-primary">Primera División</span>
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-500">22 Jugadores</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-8 pb-12">
                <PlayerCard
                    v-for="player in players"
                    :key="player.id"
                    :player="player"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
