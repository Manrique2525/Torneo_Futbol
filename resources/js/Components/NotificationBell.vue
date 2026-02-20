<script setup>
defineProps({
    count: {
        type: Number,
        default: 0
    },
    hasUrgent: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <button class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-all hover:bg-primary/10 hover:text-primary dark:bg-white/5 dark:text-slate-400">
        <span class="material-symbols-outlined">notifications</span>

        <span v-if="count > 0" class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-white bg-danger text-[10px] font-black text-white dark:border-surface-dark">
            {{ count > 9 ? '+9' : count }}
        </span>

        <span v-if="hasUrgent" class="absolute -right-1 -top-1 flex h-5 w-5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
        </span>
    </button>
</template>
