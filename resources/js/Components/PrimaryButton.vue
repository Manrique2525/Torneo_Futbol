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
        class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-primary px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-white shadow-lg shadow-primary/25 transition-all duration-200 ease-in-out hover:bg-primary-dark hover:shadow-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 dark:focus:ring-offset-background-dark active:scale-95 disabled:opacity-50 disabled:pointer-events-none"
    >
        <slot />
    </component>
</template>
