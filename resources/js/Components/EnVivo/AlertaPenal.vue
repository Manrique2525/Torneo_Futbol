<script setup>
const props = defineProps({
    show: Boolean,
    equipo: { type: String, default: '' },
});
</script>

<template>
    <transition
        enter-active-class="transition-all duration-500 ease-out"
        leave-active-class="transition-all duration-300 ease-in"
        enter-from-class="opacity-0 -translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 -translate-y-4 scale-95"
    >
        <div
            v-if="show"
            class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-red-600 text-white shadow-lg shadow-red-600/30"
        >
            <span class="material-symbols-outlined text-2xl animate-pulse">notification_important</span>
            <div>
                <p class="text-xs font-black uppercase tracking-widest">Alerta de Penal</p>
                <p class="text-sm font-bold">
                    El equipo <span class="underline decoration-2 underline-offset-2">{{ equipo }}</span> ha acumulado 5 faltas.
                </p>
            </div>
        </div>
    </transition>
</template>
