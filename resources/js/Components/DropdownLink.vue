<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: null,
    }
});
</script>

<template>
    <Link
        :href="href"
        class="group flex w-full items-center gap-3 px-4 py-3 text-start text-sm leading-5
               text-slate-600 dark:text-slate-400
               hover:bg-primary/5 hover:text-primary dark:hover:bg-primary/10 dark:hover:text-primary
               focus:outline-none focus:bg-slate-100 dark:focus:bg-white/5
               transition-all duration-200 ease-in-out font-bold uppercase tracking-tight"
    >
        <span
            v-if="icon"
            class="material-symbols-outlined !text-lg transition-transform duration-200 group-hover:scale-110"
        >
            {{ icon }}
        </span>

        <slot />
    </Link>
</template>
