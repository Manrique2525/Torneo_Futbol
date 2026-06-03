<script setup>
import { ref } from 'vue';

const props = defineProps({
    jugadores: Array,
    equipoId: [Number, String],
    equipoNombre: String,
    expulsados: Array,
    puedeRegistrar: Boolean,
});

const emit = defineEmits(['evento']);

const busqueda = ref('');

const jugadoresFiltrados = () => {
    if (!busqueda.value) return props.jugadores;
    const q = busqueda.value.toLowerCase();
    return props.jugadores.filter(j => j.nombre.toLowerCase().includes(q) || String(j.numero).includes(q));
};

const expulsado = (jugadorId) => props.expulsados?.includes(jugadorId);

const acciones = [
    { tipo: 'gol', label: 'Gol', icon: 'sports_soccer', color: 'bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white' },
    { tipo: 'autogol', label: 'Autogol', icon: 'sports_soccer', color: 'bg-orange-500/10 text-orange-600 hover:bg-orange-500 hover:text-white' },
    { tipo: 'gol_penal', label: 'Penal', icon: 'sports_soccer', color: 'bg-emerald-700/10 text-emerald-700 hover:bg-emerald-700 hover:text-white' },
    { tipo: 'tarjeta_amarilla', label: 'Amarilla', icon: 'style', color: 'bg-amber-500/10 text-amber-600 hover:bg-amber-500 hover:text-white' },
    { tipo: 'tarjeta_roja', label: 'Roja', icon: 'style', color: 'bg-red-500/10 text-red-600 hover:bg-red-500 hover:text-white' },
    { tipo: 'falta', label: 'Falta', icon: 'gavel', color: 'bg-slate-500/10 text-slate-600 hover:bg-slate-500 hover:text-white' },
];

const registrar = (jugador, tipo) => {
    emit('evento', { jugador, tipo, equipo_id: props.equipoId });
};
</script>

<template>
    <div class="space-y-3">
        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ equipoNombre }}</h3>

        <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                <span class="material-symbols-outlined text-base">search</span>
            </span>
            <input
                v-model="busqueda"
                type="text"
                placeholder="Buscar jugador..."
                class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-white/5 border border-transparent rounded-xl text-xs text-slate-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none"
            >
        </div>

        <div class="space-y-2 max-h-[500px] overflow-y-auto custom-scrollbar pr-1">
            <div
                v-for="jugador in jugadoresFiltrados()"
                :key="jugador.id"
                class="group flex items-center gap-3 p-2.5 rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-slate-800 transition-all"
                :class="expulsado(jugador.id) ? 'opacity-50 grayscale' : ''"
            >
                <div class="h-9 w-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-black shrink-0">
                    {{ jugador.numero ?? '-' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ jugador.nombre }}</p>
                    <p class="text-[10px] text-slate-400">{{ jugador.posicion }}</p>
                </div>

                <div v-if="expulsado(jugador.id)" class="shrink-0">
                    <span class="px-2 py-1 rounded-lg bg-red-500/10 text-red-600 text-[10px] font-black uppercase tracking-wide">
                        Expulsado
                    </span>
                </div>

                <div v-else-if="puedeRegistrar" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        v-for="acc in acciones"
                        :key="acc.tipo"
                        @click="registrar(jugador, acc.tipo)"
                        class="p-1.5 rounded-lg transition-all"
                        :class="acc.color"
                        :title="acc.label"
                    >
                        <span class="material-symbols-outlined !text-base">{{ acc.icon }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
