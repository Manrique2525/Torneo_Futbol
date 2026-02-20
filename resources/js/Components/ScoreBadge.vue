<script setup>
defineProps({
    value: [String, Number],
    label: {
        type: String,
        default: null
    },
    variant: {
        type: String,
        default: 'primary' // primary, secondary, danger, neutral
    },
    size: {
        type: String,
        default: 'md' // sm, md, lg
    }
});

const variants = {
    primary: 'bg-primary text-white shadow-lg shadow-primary/20',
    secondary: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    danger: 'bg-red-500 text-white shadow-lg shadow-red-500/20',
    neutral: 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
};

const sizes = {
    sm: 'h-7 min-w-[28px] text-[10px]',
    md: 'h-9 min-w-[36px] text-xs',
    lg: 'h-11 min-w-[44px] text-sm'
};
</script>

<template>
    <div class="inline-flex items-center gap-2">
        <div
            :class="[
                'flex items-center justify-center rounded-xl font-black px-2 transition-transform duration-300 hover:scale-110',
                variants[variant],
                sizes[size]
            ]"
        >
            {{ value }}
        </div>
        <span v-if="label" class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
            {{ label }}
        </span>
    </div>
</template>
