<script setup>
defineProps({
    formation: {
        type: String,
        default: '4-3-3'
    }
});
</script>

<template>
    <div class="relative aspect-[3/4] w-full max-w-2xl overflow-hidden rounded-[3rem] bg-[#2d5a27] shadow-2xl border-4 border-white/20">
        <div class="absolute inset-0 flex flex-col">
            <div v-for="i in 10" :key="i" :class="['flex-1 w-full', i % 2 === 0 ? 'bg-black/5' : 'bg-transparent']"></div>
        </div>

        <div class="absolute inset-4 border-2 border-white/30 rounded-lg">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-20 border-b-2 border-x-2 border-white/30"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 border-2 border-white/30 rounded-full"></div>
            <div class="absolute top-1/2 w-full h-px bg-white/30"></div>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-20 border-t-2 border-x-2 border-white/30"></div>
        </div>

        <div class="absolute inset-0 p-8">
            <slot />
        </div>
    </div>
</template>
