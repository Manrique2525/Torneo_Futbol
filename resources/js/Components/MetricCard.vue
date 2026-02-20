<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    trend: [String, Number],
    trendUp: { type: Boolean, default: true },
    iconColorClass: {
        type: String,
        default: 'bg-primary/10 text-primary'
    }
});
</script>

<template>
    <div class="relative overflow-hidden rounded-3xl bg-white dark:bg-surface-dark p-6 shadow-sm border border-slate-200 dark:border-slate-800 transition-all hover:shadow-md group">
        <dt>
            <div class="absolute rounded-2xl p-3 transition-transform duration-300 group-hover:scale-110" :class="iconColorClass">
                <span class="material-symbols-outlined">{{ icon }}</span>
            </div>
            <p class="ml-16 truncate text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                {{ title }}
            </p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-1">
            <p class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ value }}</p>
            <p v-if="trend" class="ml-2 flex items-baseline text-xs font-bold"
               :class="trendUp ? 'text-primary' : 'text-red-500'">
                <span class="material-symbols-outlined !text-sm self-center">
                    {{ trendUp ? 'trending_up' : 'trending_down' }}
                </span>
                {{ trend }}%
            </p>
        </dd>
    </div>
</template>
