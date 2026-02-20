<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary text-sm font-bold leading-5 text-slate-900 dark:text-white focus:outline-none transition-all duration-300 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700 focus:outline-none transition-all duration-300 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <div class="h-full flex items-center justify-center py-4 transition-transform duration-200 active:scale-95">
            <slot />
        </div>
    </Link>
</template>
