<script setup>
defineProps({
    player: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="group relative w-full max-w-[280px] overflow-hidden rounded-[2.5rem] bg-slate-900 p-1 shadow-2xl transition-all duration-500 hover:scale-105 dark:bg-primary/20">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/40 to-slate-900 opacity-50 transition-opacity group-hover:opacity-80"></div>

        <div class="relative flex flex-col items-center bg-slate-900 dark:bg-surface-dark rounded-[2.3rem] pb-6">

            <div class="flex w-full justify-between px-6 pt-8">
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-black text-white leading-none">{{ player.rating }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-primary">{{ player.position }}</span>
                </div>
                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white/10">
                    <img :src="player.team_logo" class="h-full w-full object-cover" v-if="player.team_logo" />
                    <div v-else class="flex h-full w-full items-center justify-center bg-white/5 text-[10px] font-bold text-white">
                        {{ player.team_name.substring(0,2) }}
                    </div>
                </div>
            </div>

            <div class="relative mt-2 flex h-32 items-end justify-center">
                <span class="material-symbols-outlined text-8xl text-white/10 absolute top-0">person</span>
                <div class="relative z-10 text-6xl font-black text-white/20 select-none">
                    {{ player.number }}
                </div>
            </div>

            <div class="mt-4 text-center">
                <h3 class="text-xl font-black uppercase tracking-tighter text-white">
                    {{ player.name }}
                </h3>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                    {{ player.team_name }}
                </p>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-x-4 gap-y-2 border-t border-white/5 px-6 pt-6">
                <div class="text-center" v-for="(val, stat) in player.stats" :key="stat">
                    <span class="block text-xs font-black text-white">{{ val }}</span>
                    <span class="text-[9px] font-bold uppercase text-slate-500">{{ stat }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
