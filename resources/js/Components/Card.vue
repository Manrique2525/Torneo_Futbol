<script setup>
defineProps({
    title: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        default: null,
    },
    noPadding: {
        type: Boolean,
        default: false,
    }
});
</script>

<template>
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 dark:border-slate-800 dark:bg-surface-dark">
        <div v-if="title || icon" class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 dark:border-slate-800/50">
            <span v-if="icon" class="material-symbols-outlined text-primary text-2xl">
                {{ icon }}
            </span>
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-700 dark:text-slate-200">
                {{ title }}
            </h3>
        </div>

        <div :class="[noPadding ? '' : 'p-6']">
            <slot />
        </div>

        <div v-if="$slots.footer" class="bg-slate-50/50 px-6 py-4 dark:bg-black/10 border-t border-slate-100 dark:border-slate-800/50">
            <slot name="footer" />
        </div>
    </div>
</template>
