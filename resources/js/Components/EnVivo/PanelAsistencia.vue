<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    jugadores: Array,
    equipoId: [Number, String],
    equipoNombre: String,
    asistencias: Object, // keyBy jugador_id
    mitad: { type: Number, default: 1 }, // 1 o 2
    puedeRegistrar: Boolean,
});

const emit = defineEmits(['guardar']);

const inicializar = () => {
    return (props.jugadores || []).map(j => {
        const existente = props.asistencias?.[j.id];
        return {
            jugador_id: j.id,
            equipo_id: props.equipoId,
            nombre: j.nombre,
            numero: j.numero,
            asistio_primera_mitad: existente?.asistio_primera_mitad ?? null,
            asistio_segunda_mitad: existente?.asistio_segunda_mitad ?? null,
        };
    });
};

const lista = ref(inicializar());

watch(() => [props.jugadores, props.asistencias], () => {
    lista.value = inicializar();
}, { deep: true });

const toggle = (item, campo) => {
    if (!props.puedeRegistrar) return;
    item[campo] = item[campo] === true ? false : (item[campo] === false ? null : true);
};

const guardar = () => {
    emit('guardar', lista.value.map(i => ({
        jugador_id: i.jugador_id,
        equipo_id: i.equipo_id,
        asistio_primera_mitad: i.asistio_primera_mitad,
        asistio_segunda_mitad: i.asistio_segunda_mitad,
    })));
};

const campoVisible = (mitad) => mitad === 1 ? 'asistio_primera_mitad' : 'asistio_segunda_mitad';
const labelVisible = (mitad) => mitad === 1 ? '1ª Mitad' : '2ª Mitad';
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                Pase de lista — {{ equipoNombre }}
            </h3>
            <span v-if="!puedeRegistrar" class="text-[10px] font-bold text-red-500 uppercase tracking-wide">Solo lectura</span>
        </div>

        <div class="space-y-1.5 max-h-[280px] sm:max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
            <div
                v-for="item in lista"
                :key="item.jugador_id"
                class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-slate-800"
            >
                <div class="flex items-center gap-3">
                    <span class="h-7 w-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-[10px] font-black">
                        {{ item.numero ?? '-' }}
                    </span>
                    <span class="text-xs font-bold text-slate-800 dark:text-white">{{ item.nombre }}</span>
                </div>

                <button
                    v-if="puedeRegistrar"
                    @click="toggle(item, campoVisible(mitad))"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wide transition-all border"
                    :class="{
                        'bg-emerald-500/10 text-emerald-600 border-emerald-500/20': item[campoVisible(mitad)] === true,
                        'bg-red-500/10 text-red-600 border-red-500/20': item[campoVisible(mitad)] === false,
                        'bg-slate-100 text-slate-400 border-slate-200 dark:bg-white/5 dark:border-slate-700': item[campoVisible(mitad)] === null,
                    }"
                >
                    <span class="material-symbols-outlined !text-base">
                        {{ item[campoVisible(mitad)] === true ? 'check_circle' : (item[campoVisible(mitad)] === false ? 'cancel' : 'help') }}
                    </span>
                    {{ labelVisible(mitad) }}
                </button>

                <span
                    v-else
                    class="text-[10px] font-bold uppercase tracking-wide"
                    :class="{
                        'text-emerald-600': item[campoVisible(mitad)] === true,
                        'text-red-600': item[campoVisible(mitad)] === false,
                        'text-slate-400': item[campoVisible(mitad)] === null,
                    }"
                >
                    {{ item[campoVisible(mitad)] === true ? 'Asistió' : (item[campoVisible(mitad)] === false ? 'No asistió' : 'Sin registrar') }}
                </span>
            </div>
        </div>

        <button
            v-if="puedeRegistrar"
            @click="guardar"
            class="w-full py-2.5 rounded-xl bg-primary text-white font-bold uppercase tracking-wider text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            Guardar asistencias
        </button>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
