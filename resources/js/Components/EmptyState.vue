<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    title: {
        type: String,
        default: 'No hay datos disponibles'
    },
    description: {
        type: String,
        default: 'Parece que aún no hay registros en esta sección.'
    },
    icon: {
        type: String,
        default: 'sports_soccer'
    },
    actionLabel: {
        type: String,
        default: null
    }
});

defineEmits(['action']);
</script>

<template>
    <div class="flex flex-col items-center justify-center p-12 text-center bg-slate-50/50 dark:bg-white/5 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
        <div class="relative mb-6">
            <div class="absolute -inset-4 rounded-full bg-primary/10 blur-2xl dark:bg-primary/5"></div>
            <div class="relative flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-surface-dark dark:ring-slate-700">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">
                    {{ icon }}
                </span>
            </div>
            <div class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-primary text-white shadow-lg border-4 border-white dark:border-[#1A2C26]">
                <span class="material-symbols-outlined text-base">add</span>
            </div>
        </div>

        <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
            {{ title }}
        </h3>
        <p class="mt-2 max-w-xs text-sm font-medium text-slate-500 dark:text-slate-400">
            {{ description }}
        </p>

        <div v-if="actionLabel" class="mt-8">
            <PrimaryButton @click="$emit('action')">
                <span class="material-symbols-outlined">add</span>
                {{ actionLabel }}
            </PrimaryButton>
        </div>
    </div>
</template>
