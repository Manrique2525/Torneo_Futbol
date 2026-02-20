<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    message: String,
    type: { type: String, default: 'success' } // success, error, info
});

const emit = defineEmits(['close']);

onMounted(() => {
    setTimeout(() => emit('close'), 3000);
});
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-[1.5rem] bg-white shadow-2xl ring-1 ring-black ring-opacity-5 dark:bg-surface-dark dark:ring-white/10">
            <div class="p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <span v-if="type === 'success'" class="material-symbols-outlined text-primary">check_circle</span>
                        <span v-else class="material-symbols-outlined text-red-500">error</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">
                            {{ message }}
                        </p>
                    </div>
                    <button @click="$emit('close')" class="text-slate-400 hover:text-slate-500">
                        <span class="material-symbols-outlined !text-sm">close</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
