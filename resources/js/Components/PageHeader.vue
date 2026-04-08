<script setup>
defineProps({
    title:       { type: String, required: true },
    description: { type: String, default: '' },
})
</script>

<template>
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-5">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ title }}
                </h1>
                <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-3">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
