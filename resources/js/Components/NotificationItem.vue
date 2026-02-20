<script setup>
defineProps({
    title: String,
    description: String,
    time: String,
    type: {
        type: String,
        default: 'info' // info, success, warning
    },
    read: {
        type: Boolean,
        default: false
    }
});

const icons = {
    info: { name: 'info', class: 'text-blue-500 bg-blue-500/10' },
    success: { name: 'check_circle', class: 'text-primary bg-primary/10' },
    warning: { name: 'emergency', class: 'text-orange-500 bg-orange-500/10' }
};
</script>

<template>
    <div
        class="flex gap-4 px-4 py-4 transition-colors hover:bg-slate-50 dark:hover:bg-white/5 border-b border-slate-100 dark:border-slate-800/50 last:border-0"
        :class="{'opacity-60': read}"
    >
        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', icons[type].class]">
            <span class="material-symbols-outlined !text-xl">{{ icons[type].name }}</span>
        </div>

        <div class="flex flex-col gap-0.5">
            <h4 class="text-xs font-black uppercase tracking-tight text-slate-900 dark:text-white">
                {{ title }}
            </h4>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 line-clamp-2">
                {{ description }}
            </p>
            <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                {{ time }}
            </span>
        </div>

        <div v-if="!read" class="ml-auto flex items-center">
            <div class="h-2 w-2 rounded-full bg-primary"></div>
        </div>
    </div>
</template>
