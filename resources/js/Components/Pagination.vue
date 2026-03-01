<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-400 bg-slate-100/50 dark:bg-white/5 rounded-xl border border-transparent"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-4 py-2 text-xs font-black uppercase tracking-widest transition-all duration-200 rounded-xl border"
                :class="[
                    link.active
                        ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20 scale-110 z-10'
                        : 'bg-white dark:bg-[#1A2C26] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:border-primary hover:text-primary'
                ]"
                v-html="link.label"
            />
        </template>
    </div>
</template>
