<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const componentType = computed(() => {
    return props.href ? Link : 'button';
});
</script>

<template>
    <component
        :is="componentType"
        :href="href"
        :type="!href ? type : null"
        :disabled="disabled"
        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-surface-dark px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300 transition-all duration-200 ease-in-out hover:bg-slate-50 dark:hover:bg-white/5 hover:text-primary dark:hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-2 dark:focus:ring-offset-surface-dark active:scale-95 disabled:opacity-50 disabled:pointer-events-none shadow-sm"
    >
        <slot />
    </component>
</template>
