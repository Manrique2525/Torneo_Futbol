<script setup>
defineProps({
    tabs: {
        type: Array, // Array de strings o objetos { id, label }
        required: true
    },
    modelValue: [String, Number]
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="inline-flex p-1.5 bg-slate-100 dark:bg-white/5 rounded-[20px] border border-slate-200/50 dark:border-slate-800/50">
        <button
            v-for="tab in tabs"
            :key="tab.id || tab"
            @click="$emit('update:modelValue', tab.id || tab)"
            :class="[
                'px-6 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-2xl',
                modelValue === (tab.id || tab)
                    ? 'bg-white dark:bg-primary text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200 dark:ring-transparent'
                    : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            ]"
        >
            {{ tab.label || tab }}
        </button>
    </div>
</template>
