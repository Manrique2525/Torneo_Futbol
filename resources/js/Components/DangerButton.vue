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
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-red-600 px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-[#1A2C26] active:scale-95 disabled:opacity-50 shadow-sm"
    >
        <slot />
    </component>
</template>
