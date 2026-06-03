<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    eventos: Array,
    puedeEliminar: Boolean,
    equipoLocalId: [Number, String],
    equipoVisitanteId: [Number, String],
});

const emit = defineEmits(['eliminar']);

const esLocal = (equipoId) => Number(equipoId) === Number(props.equipoLocalId);
</script>

<template>
    <div class="space-y-3">
        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Eventos</h3>

        <div v-if="!eventos?.length" class="text-center py-6 text-slate-400 text-xs">
            Sin eventos registrados
        </div>

        <div
            v-for="evt in eventos"
            :key="evt.id"
            class="group flex items-center gap-3 p-3 rounded-xl border transition-all"
            :class="[
                evt.tipo_color,
                esLocal(evt.equipo_id) ? 'flex-row' : 'flex-row-reverse',
                'border-opacity-50'
            ]"
        >
            <div class="flex items-center justify-center h-8 w-8 rounded-lg bg-white/60 dark:bg-black/20 shrink-0">
                <span class="material-symbols-outlined text-lg">{{ evt.tipo_icon }}</span>
            </div>

            <div class="flex-1 min-w-0" :class="esLocal(evt.equipo_id) ? 'text-left' : 'text-right'">
                <p class="text-xs font-bold truncate">
                    {{ evt.minuto }}' — {{ evt.tipo_label }}
                </p>
                <p v-if="evt.jugador" class="text-[10px] font-medium opacity-80 truncate">
                    #{{ evt.jugador.numero }} {{ evt.jugador.nombre }}
                    <span v-if="evt.jugador_relacionado" class="opacity-60">
                        → #{{ evt.jugador_relacionado.numero }} {{ evt.jugador_relacionado.nombre }}
                    </span>
                </p>
                <p v-if="evt.comentario" class="text-[10px] opacity-60 truncate">{{ evt.comentario }}</p>
            </div>

            <button
                v-if="puedeEliminar"
                @click="emit('eliminar', evt)"
                class="shrink-0 p-1.5 rounded-lg hover:bg-red-500 hover:text-white transition-colors text-red-600 opacity-0 group-hover:opacity-100 focus:opacity-100"
                title="Eliminar evento"
            >
                <span class="material-symbols-outlined !text-base">delete</span>
            </button>
        </div>
    </div>
</template>
