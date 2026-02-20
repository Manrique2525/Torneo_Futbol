<script setup>
defineProps({
    player: Object,
    positionClass: String // Clases de Tailwind para posicionar (top-x, left-x)
});
</script>

<template>
    <div :class="['absolute flex flex-col items-center gap-1 transition-all duration-500', positionClass]">
        <div class="group relative">
            <div class="absolute -inset-2 rounded-full bg-primary/20 blur opacity-0 group-hover:opacity-100 transition-opacity"></div>

            <div class="relative h-12 w-12 rounded-full border-2 border-white bg-slate-900 shadow-lg flex items-center justify-center text-white font-black text-xs">
                {{ player?.number || '?' }}
            </div>

            <div class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-primary flex items-center justify-center text-[8px] font-black text-white border border-white">
                {{ player?.rating || '80' }}
            </div>
        </div>

        <span class="rounded-md bg-black/60 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-white backdrop-blur-sm">
            {{ player?.name.split(' ').pop() || 'Vacío' }}
        </span>
    </div>
</template>
