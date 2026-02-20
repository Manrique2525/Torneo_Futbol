<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-slate-300 text-primary shadow-sm
               focus:ring-primary focus:ring-offset-2
               dark:border-slate-700 dark:bg-[#0F1D19] dark:focus:ring-offset-[#1A2C26]
               transition-colors duration-150 cursor-pointer"
    />
</template>
