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
        ? 'block w-full ps-3 pe-4 py-3 border-l-4 border-primary text-start text-sm font-bold text-primary bg-primary/10 dark:bg-primary/20 focus:outline-none transition-all duration-300 ease-in-out'
        : 'block w-full ps-3 pe-4 py-3 border-l-4 border-transparent text-start text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 hover:border-slate-300 dark:hover:border-slate-700 focus:outline-none transition-all duration-300 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <div class="flex items-center gap-2">
            <slot />
        </div>
    </Link>
</template>
