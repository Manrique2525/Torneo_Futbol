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
const accionSeleccionada = ref(null);

const jugadoresFiltrados = () => {
    if (!busqueda.value) return props.jugadores;
    const q = busqueda.value.toLowerCase();
    return props.jugadores.filter(j => j.nombre.toLowerCase().includes(q) || String(j.numero).includes(q));
};

const expulsado = (jugadorId) => props.expulsados?.includes(jugadorId);

const acciones = [
    { tipo: 'gol', label: 'Gol', icon: 'sports_soccer', color: 'bg-emerald-500/10 text-emerald-600', activeColor: 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' },
    { tipo: 'autogol', label: 'Autogol', icon: 'sports_soccer', color: 'bg-orange-500/10 text-orange-600', activeColor: 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' },
    { tipo: 'gol_penal', label: 'Penal', icon: 'sports_soccer', color: 'bg-emerald-700/10 text-emerald-700', activeColor: 'bg-emerald-700 text-white shadow-lg shadow-emerald-700/30' },
    { tipo: 'tarjeta_amarilla', label: 'Amarilla', icon: 'style', color: 'bg-amber-500/10 text-amber-600', activeColor: 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' },
    { tipo: 'tarjeta_roja', label: 'Roja', icon: 'style', color: 'bg-red-500/10 text-red-600', activeColor: 'bg-red-500 text-white shadow-lg shadow-red-500/30' },
    { tipo: 'falta', label: 'Falta', icon: 'gavel', color: 'bg-slate-500/10 text-slate-600', activeColor: 'bg-slate-500 text-white shadow-lg shadow-slate-500/30' },
];

const seleccionarAccion = (tipo) => {
    if (accionSeleccionada.value === tipo) {
        accionSeleccionada.value = null;
        return;
    }
    accionSeleccionada.value = tipo;
};

const registrar = (jugador) => {
    if (!accionSeleccionada.value) return;
    emit('evento', { jugador, tipo: accionSeleccionada.value, equipo_id: props.equipoId });
    accionSeleccionada.value = null;
};
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ equipoNombre }}</h3>
            <div v-if="accionSeleccionada" class="flex items-center gap-1.5">
                <span class="text-[10px] font-black uppercase tracking-wider text-primary animate-pulse">Selecciona jugador</span>
                <button @click="accionSeleccionada = null" class="p-0.5 rounded-full hover:bg-slate-100 dark:hover:bg-white/10 transition-all" title="Cancelar">
                    <span class="material-symbols-outlined !text-sm text-slate-400">close</span>
                </button>
            </div>
        </div>

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

        <div v-if="puedeRegistrar" class="flex flex-wrap gap-1">
            <button
                v-for="acc in acciones"
                :key="acc.tipo"
                @click="seleccionarAccion(acc.tipo)"
                class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all"
                :class="accionSeleccionada === acc.tipo ? acc.activeColor : acc.color"
            >
                <span class="material-symbols-outlined !text-sm">{{ acc.icon }}</span>
                <span class="hidden sm:inline">{{ acc.label }}</span>
            </button>
        </div>

        <div class="space-y-1.5">
            <div
                v-for="jugador in jugadoresFiltrados()"
                :key="jugador.id"
                class="flex items-center gap-3 p-2.5 rounded-xl transition-all"
                :class="[
                    expulsado(jugador.id)
                        ? 'opacity-50 grayscale bg-white dark:bg-white/5 border border-slate-100 dark:border-slate-800'
                        : accionSeleccionada && puedeRegistrar
                            ? 'bg-primary/5 border border-primary/20 cursor-pointer hover:bg-primary/10 hover:border-primary/30 hover:shadow-md hover:shadow-primary/10 active:scale-[0.98]'
                            : 'bg-white dark:bg-white/5 border border-slate-100 dark:border-slate-800',
                ]"
                @click="accionSeleccionada && !expulsado(jugador.id) && registrar(jugador)"
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

                <div v-else-if="accionSeleccionada" class="shrink-0">
                    <span class="h-7 w-7 rounded-full bg-primary/20 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined !text-base">arrow_forward</span>
                    </span>
                </div>
            </div>

            <div v-if="jugadoresFiltrados().length === 0" class="text-center py-6">
                <p class="text-xs text-slate-400 font-medium">Sin jugadores</p>
            </div>
        </div>
    </div>
</template>
