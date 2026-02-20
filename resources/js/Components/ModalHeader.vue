<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);
</script>

<template>
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800/50 bg-slate-50/30 dark:bg-white/5">
        <div class="flex flex-col">
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none">
                {{ title }}
            </h3>
            <p v-if="subtitle" class="mt-1 text-xs font-bold text-primary uppercase tracking-widest">
                {{ subtitle }}
            </p>
        </div>

        <button
            @click="emit('close')"
            class="flex items-center justify-center h-10 w-10 rounded-xl text-slate-400 hover:text-danger hover:bg-danger/10 dark:hover:bg-danger/20 transition-all duration-200 active:scale-90"
        >
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
    </div>
</template>
