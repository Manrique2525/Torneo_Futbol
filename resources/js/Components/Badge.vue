<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    dot: { type: Boolean, default: false },
})
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            {
                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300':       variant === 'default',
                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': variant === 'success',
                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': variant === 'warning',
                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400':         variant === 'danger',
                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400':     variant === 'info',
            },
        ]"
    >
        <span
            v-if="dot"
            :class="[
                'w-1.5 h-1.5 rounded-full mr-1.5',
                {
                    'bg-gray-500':  variant === 'default',
                    'bg-green-500': variant === 'success',
                    'bg-amber-500': variant === 'warning',
                    'bg-red-500':   variant === 'danger',
                    'bg-blue-500':  variant === 'info',
                },
            ]"
        ></span>
        <slot />
    </span>
</template>
