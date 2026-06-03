<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';

const props = defineProps({
    show: Boolean,
    jugadorPre: Object,
    tipoPre: String,
    equipoId: [Number, String],
    jugadores: Array,
    tiposEvento: Array,
    duracion: { type: Number, default: 90 },
});

const emit = defineEmits(['close', 'guardado']);

const form = useForm({
    equipo_id: props.equipoId,
    jugador_id: props.jugadorPre?.id ?? null,
    jugador_relacionado_id: null,
    tipo: props.tipoPre ?? '',
    minuto: 1,
    comentario: '',
});

const selectedJugador = ref(null);
const selectedJugadorRelacionado = ref(null);
const selectedTipo = ref(null);

watch(() => props.show, (val) => {
    if (val) {
        form.equipo_id = props.equipoId;
        form.jugador_id = props.jugadorPre?.id ?? null;
        form.jugador_relacionado_id = null;
        form.tipo = props.tipoPre ?? '';
        form.minuto = Math.min(form.minuto || 1, props.duracion);
        form.comentario = '';
        selectedJugador.value = props.jugadores.find(j => j.id === props.jugadorPre?.id) ?? null;
        selectedJugadorRelacionado.value = null;
        selectedTipo.value = props.tiposEvento.find(t => t.value === props.tipoPre) ?? null;
    }
});

const jugadorOptions = (props.jugadores || []).map(j => ({ label: `#${j.numero} ${j.nombre}`, value: j.id }));
const tipoOptions = (props.tiposEvento || []).map(t => ({ label: t.label, value: t.value }));

const onJugadorChange = (o) => form.jugador_id = o?.value ?? null;
const onJugadorRelChange = (o) => form.jugador_relacionado_id = o?.value ?? null;
const onTipoChange = (o) => form.tipo = o?.value ?? '';

const esSustitucion = () => ['sustitucion_entrada', 'sustitucion_salida'].includes(form.tipo);
const requiereJugador = () => form.tipo && form.tipo !== 'penal_concedido';

const submit = () => {
    form.post(route('partidos.en-vivo.eventos.store', { partido: route().params.partido }), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('guardado');
            emit('close');
        },
    });
};

const close = () => {
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" maxWidth="md" @close="close">
        <div class="p-6 space-y-5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                    <span class="material-symbols-outlined text-xl">edit_note</span>
                </div>
                <div>
                    <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">Registrar Evento</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Completa los datos del evento</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Tipo *</label>
                    <VSelectCustom
                        v-model="selectedTipo"
                        :options="tipoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar tipo..."
                        @update:modelValue="onTipoChange"
                    />
                </div>

                <div v-if="requiereJugador()">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Jugador *</label>
                    <VSelectCustom
                        v-model="selectedJugador"
                        :options="jugadorOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar jugador..."
                        @update:modelValue="onJugadorChange"
                    />
                </div>

                <div v-if="esSustitucion()">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Jugador relacionado *</label>
                    <VSelectCustom
                        v-model="selectedJugadorRel"
                        :options="jugadorOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar jugador..."
                        @update:modelValue="onJugadorRelChange"
                    />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Minuto *</label>
                    <input
                        v-model="form.minuto"
                        type="number"
                        :min="1"
                        :max="duracion"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2.5 px-4 text-sm text-slate-800 dark:text-white transition-all outline-none"
                    >
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Comentario</label>
                    <textarea
                        v-model="form.comentario"
                        rows="2"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2.5 px-4 text-sm text-slate-800 dark:text-white transition-all outline-none resize-none"
                        placeholder="Opcional..."
                    ></textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    @click="close"
                    class="flex-1 py-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider text-xs hover:bg-slate-200 dark:hover:bg-white/10 transition-all"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="flex-1 py-2.5 rounded-xl bg-primary text-white font-bold uppercase tracking-wider text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50"
                >
                    Guardar
                </button>
            </div>
        </div>
    </Modal>
</template>
