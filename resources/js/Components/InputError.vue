<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
    >
        <div v-show="message">
            <p class="text-xs font-bold text-danger dark:text-red-400 mt-2 flex items-center gap-1.5 ml-1">
                <span class="material-symbols-outlined !text-sm">
                    error
                </span>
                {{ message }}
            </p>
        </div>
    </Transition>
</template>
