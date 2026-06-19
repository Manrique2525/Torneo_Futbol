<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    torneo: Object,
    jornadas: Array,
    bracket: Array,
    canchas: Array,
    arbitros: Array,
    equipos_disponibles: Array,
    constantes: Object,
    flash: Object,
});

const activeTab = ref('regular');
const showSustituirModal = ref(false);
const showEditModal = ref(false);
const showDeleteConfirm = ref(false);
const selectedPartido = ref(null);

const tieneCalendario = computed(() => {
    return (props.jornadas && props.jornadas.length > 0) || (props.bracket && props.bracket.length > 0);
});

const tienePlayoff = computed(() => {
    return props.torneo.tiene_playoff && props.bracket && props.bracket.length > 0;
});

const canchaOptions = computed(() =>
    (props.canchas || []).map(c => ({ label: c.nombre, value: c.id }))
);

const arbitroOptions = computed(() =>
    (props.arbitros || []).map(a => ({ label: a.nombre, value: a.id }))
);

const faseLabels = props.constantes?.fases_partido || {};
const motivoOptions = Object.entries(props.constantes?.motivos_sustitucion || {}).map(([k, v]) => ({ label: v, value: k }));
const resolucionOptions = Object.entries(props.constantes?.tipos_resolucion || {}).map(([k, v]) => ({ label: v, value: k }));

const goToPreview = () => {
    router.get(route('calendario.preview', props.torneo.id));
};

const deleteCalendar = () => {
    router.delete(route('calendario.destroy', props.torneo.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
        }
    });
};

const openSustituirModal = (partido) => {
    selectedPartido.value = partido;
    showSustituirModal.value = true;
};

const openEditModal = (partido) => {
    selectedPartido.value = partido;
    editForm.partidos[0].id = partido.id;
    editForm.partidos[0].cancha_id = partido.cancha?.id ?? null;
    editForm.partidos[0].fecha = partido.fecha;
    editForm.partidos[0].hora = partido.hora;
    selectedCanchaEdit.value = partido.cancha
        ? canchaOptions.value.find(c => c.value === partido.cancha.id) ?? null
        : null;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedPartido.value = null;
    editForm.reset();
    selectedCanchaEdit.value = null;
};

const submitEdit = () => {
    editForm.put(route('calendario.update', props.torneo.id), {
        onSuccess: () => {
            closeEditModal();
        }
    });
};

const sustituirForm = useForm({
    equipo_original_id: null,
    equipo_sustituto_id: null,
    motivo: null,
    tipo_resolucion: null,
    nueva_fecha: null,
    nueva_hora: null,
    cancha_id: null,
    arbitro_id: null,
    notas: '',
});

const editForm = useForm({
    partidos: [{
        id: null,
        cancha_id: null,
        fecha: null,
        hora: null,
    }],
});

const selectedCanchaEdit = ref(null);

const selectedMotivo = ref(null);
const selectedResolucion = ref(null);
const selectedSustituto = ref(null);
const selectedCanchaSust = ref(null);
const selectedArbitroSust = ref(null);
const selectedOriginal = ref(null);

const equipoOptions = computed(() =>
    (props.equipos_disponibles || []).map(e => ({ label: e.nombre, value: e.id }))
);

const equipoOriginalOptions = computed(() => {
    if (!selectedPartido.value) return [];
    return equipoOptions.value.filter(e =>
        e.value === selectedPartido.value.equipo_local?.id ||
        e.value === selectedPartido.value.equipo_visitante?.id
    );
});

const equipoSustitutoOptions = computed(() => {
    if (!selectedOriginal.value) return equipoOptions.value;
    return equipoOptions.value.filter(e => e.value !== selectedOriginal.value.value);
});

const sustitucionInvalida = computed(() => {
    return !selectedOriginal.value ||
        !selectedSustituto.value ||
        selectedOriginal.value.value === selectedSustituto.value.value;
});

watch(selectedOriginal, (nuevo) => {
    if (nuevo && selectedSustituto.value && selectedSustituto.value.value === nuevo.value) {
        selectedSustituto.value = null;
        sustituirForm.equipo_sustituto_id = null;
    }
});

const submitSustitucion = () => {
    sustituirForm.post(route('calendario.sustituir', selectedPartido.value.id), {
        onSuccess: () => {
            showSustituirModal.value = false;
            sustituirForm.reset();
            selectedPartido.value = null;
        }
    });
};

const getStatusBadge = (estado) => {
    const badges = {
        programado: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        en_juego: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        descanso: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        finalizado: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
        suspendido: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        cancelado: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return badges[estado] || badges.programado;
};

const getFaseBadge = (fase) => {
    const badges = {
        regular: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        octavos: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        cuartos: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        semifinal: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        final: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    };
    return badges[fase] || badges.regular;
};
</script>

<template>
<Head title="Calendario" />

<AuthenticatedLayout>
<div class="w-full">

    <Link
        :href="route('torneos.index')"
        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
               text-slate-400 hover:text-primary focus:outline-none focus-visible:ring-2
               focus-visible:ring-primary/50 active:scale-95 transition-all mb-6 rounded-lg px-2 py-1"
    >
        <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
        Volver a torneos
    </Link>

    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <div class="flex items-center justify-between px-4 md:px-8 py-4 md:py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                    <span class="material-symbols-outlined text-xl">calendar_month</span>
                </div>
                <div>
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                        Calendario: {{ torneo.nombre }}
                    </h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[11px] text-slate-400 font-medium capitalize">{{ torneo.tipo }}</span>
                        <span v-if="torneo.ida_y_vuelta" class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-bold">Ida y Vuelta</span>
                        <span v-if="torneo.tiene_playoff" class="text-[10px] bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full font-bold">Playoff {{ torneo.playoff_equipos }} equipos</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="!tieneCalendario"
                    @click="goToPreview"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all"
                >
                    <span class="material-symbols-outlined !text-base">auto_awesome</span>
                    Generar Calendario
                </button>

                <template v-if="tieneCalendario">
                    <button
                        @click="goToPreview"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-lg shadow-amber-500/20 hover:bg-amber-600 transition-all"
                    >
                        <span class="material-symbols-outlined !text-base">refresh</span>
                        Regenerar
                    </button>
                    <button
                        @click="showDeleteConfirm = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all"
                    >
                        <span class="material-symbols-outlined !text-base">delete</span>
                        Eliminar
                    </button>
                </template>
            </div>
        </div>

        <div class="px-4 md:px-8 py-5 md:py-7">

            <div
                v-if="flash?.success"
                class="mb-6 bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
            >
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
            </div>

            <div
                v-if="flash?.error"
                class="mb-6 bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
            >
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ flash.error }}</span>
            </div>

            <div v-if="!tieneCalendario" class="text-center py-16">
                <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600">event_busy</span>
                <p class="text-slate-400 text-sm mt-4">No hay calendario generado para este torneo.</p>
                <p class="text-slate-400 text-xs mt-1">Haz clic en "Generar Calendario" para crear los enfrentamientos automáticamente.</p>
            </div>

            <template v-if="tieneCalendario">

                <div v-if="tienePlayoff" class="flex gap-2 mb-6">
                    <button
                        @click="activeTab = 'regular'"
                        :class="activeTab === 'regular' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
                    >
                        Fase Regular
                    </button>
                    <button
                        @click="activeTab = 'playoff'"
                        :class="activeTab === 'playoff' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
                    >
                        Playoff
                    </button>
                </div>

                <div v-show="activeTab === 'regular'" class="space-y-4">
                    <div v-for="jornada in jornadas" :key="jornada.id" class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 dark:bg-white/5">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary !text-lg">event</span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">{{ jornada.nombre }}</h4>
                                    <p class="text-[11px] text-slate-400">{{ jornada.fecha_inicio || 'Sin fecha' }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold" :class="jornada.estado === 'finalizada' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'">
                                {{ jornada.estado }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div v-for="partido in jornada.partidos" :key="partido.id" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex items-center gap-2 min-w-[140px]">
                                        <span class="text-xs text-slate-400">{{ partido.hora || '--:--' }}</span>
                                        <span v-if="partido.cancha" class="text-[10px] text-slate-400 truncate max-w-[80px]">{{ partido.cancha.nombre }}</span>
                                        <span class="text-[10px] text-slate-400">{{ partido.duracion_minutos }}'</span>
                                    </div>

                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="text-right flex-1">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ partido.equipo_local?.nombre || 'TBD' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 px-3">
                                            <span class="text-lg font-black text-slate-800 dark:text-white">{{ partido.goles_local ?? '-' }}</span>
                                            <span class="text-xs text-slate-400">vs</span>
                                            <span class="text-lg font-black text-slate-800 dark:text-white">{{ partido.goles_visitante ?? '-' }}</span>
                                        </div>
                                        <div class="text-left flex-1">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ partido.equipo_visitante?.nombre || 'TBD' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" :class="getFaseBadge(partido.fase)">
                                        {{ faseLabels[partido.fase] || partido.fase }}
                                    </span>
                                    <span v-if="partido.es_vuelta" class="text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 px-2 py-0.5 rounded-full font-bold">Vuelta</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" :class="getStatusBadge(partido.estado)">
                                        {{ partido.estado }}
                                    </span>

                                    <button
                                        v-if="partido.estado === 'programado'"
                                        @click="openEditModal(partido)"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                                        title="Editar cancha, fecha y hora"
                                    >
                                        <span class="material-symbols-outlined !text-base">edit</span>
                                    </button>
                                    <button
                                        v-if="partido.estado === 'programado'"
                                        @click="openSustituirModal(partido)"
                                        class="p-1.5 text-slate-400 hover:text-amber-500 transition-colors"
                                        title="Sustituir equipo"
                                    >
                                        <span class="material-symbols-outlined !text-base">swap_horiz</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'playoff'" class="space-y-6">
                    <div v-for="fase in bracket" :key="fase.fase" class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 dark:bg-white/5">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-500 !text-lg">emoji_events</span>
                                {{ fase.label }}
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                            <div v-for="partido in fase.partidos" :key="partido.id" class="border border-slate-100 dark:border-slate-700 rounded-xl p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] text-slate-400 font-bold">{{ partido.llave_bracket }}</span>
                                    <span v-if="partido.es_vuelta" class="text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 px-2 py-0.5 rounded-full font-bold">Vuelta</span>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">
                                            {{ partido.equipo_local?.nombre || 'Por definir' }}
                                        </span>
                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ partido.goles_local ?? '-' }}</span>
                                    </div>
                                    <div class="border-t border-slate-100 dark:border-slate-700"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">
                                            {{ partido.equipo_visitante?.nombre || 'Por definir' }}
                                        </span>
                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ partido.goles_visitante ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>{{ partido.fecha || 'Sin fecha' }} {{ partido.hora || '' }} {{ partido.duracion_minutos }}'</span>
                                    <div class="flex items-center gap-2">
                                        <span v-if="partido.cancha">{{ partido.cancha.nombre }}</span>
                                        <button
                                            v-if="partido.estado === 'programado'"
                                            @click="openEditModal(partido)"
                                            class="p-1 text-slate-400 hover:text-primary transition-colors"
                                            title="Editar cancha, fecha y hora"
                                        >
                                            <span class="material-symbols-outlined !text-base">edit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Sustituir Modal -->
<div v-if="showSustituirModal && selectedPartido" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-2xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                Sustituir Equipo
            </h3>
            <button @click="showSustituirModal = false" class="p-2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="submitSustitucion" class="p-6 space-y-4">
            <div class="p-3 bg-slate-50 dark:bg-white/5 rounded-xl text-xs text-slate-600 dark:text-slate-400">
                <strong>{{ selectedPartido.equipo_local?.nombre }}</strong> vs <strong>{{ selectedPartido.equipo_visitante?.nombre }}</strong>
                <br>{{ selectedPartido.fecha }} {{ selectedPartido.hora }}
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Equipo Original</label>
                <VSelectCustom
                    v-model="selectedOriginal"
                    :options="equipoOriginalOptions"
                    label="label"
                    :clearable="false"
                    placeholder="Seleccionar..."
                    @update:modelValue="(o) => { selectedOriginal = o; sustituirForm.equipo_original_id = o?.value }"
                />
                <InputError :message="sustituirForm.errors.equipo_original_id" class="mt-1" />
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Equipo Sustituto</label>
                <VSelectCustom
                    v-model="selectedSustituto"
                    :options="equipoSustitutoOptions"
                    label="label"
                    :clearable="false"
                    placeholder="Seleccionar sustituto..."
                    @update:modelValue="(o) => { selectedSustituto = o; sustituirForm.equipo_sustituto_id = o?.value }"
                />
                <InputError :message="sustituirForm.errors.equipo_sustituto_id" class="mt-1" />
                <p
                    v-if="selectedOriginal && selectedSustituto && selectedOriginal.value === selectedSustituto.value"
                    class="text-[11px] text-red-600 mt-1 font-bold"
                >
                    El equipo sustituto no puede ser el mismo que el original.
                </p>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Motivo</label>
                <VSelectCustom
                    v-model="selectedMotivo"
                    :options="motivoOptions"
                    label="label"
                    :clearable="false"
                    placeholder="Seleccionar motivo..."
                    @update:modelValue="(o) => { selectedMotivo = o; sustituirForm.motivo = o?.value }"
                />
                <InputError :message="sustituirForm.errors.motivo" class="mt-1" />
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Tipo de Resolución</label>
                <VSelectCustom
                    v-model="selectedResolucion"
                    :options="resolucionOptions"
                    label="label"
                    :clearable="false"
                    placeholder="Seleccionar..."
                    @update:modelValue="(o) => { selectedResolucion = o; sustituirForm.tipo_resolucion = o?.value }"
                />
                <InputError :message="sustituirForm.errors.tipo_resolucion" class="mt-1" />
            </div>

            <template v-if="sustituirForm.tipo_resolucion === 'reprogramado'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Nueva Fecha</label>
                        <input type="date" v-model="sustituirForm.nueva_fecha" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                        <InputError :message="sustituirForm.errors.nueva_fecha" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Nueva Hora</label>
                        <input type="time" v-model="sustituirForm.nueva_hora" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                        <InputError :message="sustituirForm.errors.nueva_hora" class="mt-1" />
                    </div>
                </div>
            </template>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Notas</label>
                <textarea v-model="sustituirForm.notas" rows="2" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none resize-none" placeholder="Notas adicionales..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showSustituirModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <PrimaryButton type="submit" :disabled="sustituirForm.processing || sustitucionInvalida" class="px-6 py-2 text-xs">
                    Confirmar Sustitución
                </PrimaryButton>
            </div>
        </form>
    </div>
</div>

<!-- Edit Partido Modal -->
<div v-if="showEditModal && selectedPartido" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-2xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                Editar Partido
            </h3>
            <button @click="closeEditModal" class="p-2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="submitEdit" class="p-6 space-y-4">
            <div class="p-3 bg-slate-50 dark:bg-white/5 rounded-xl text-xs text-slate-600 dark:text-slate-400">
                <strong>{{ selectedPartido.equipo_local?.nombre }}</strong> vs <strong>{{ selectedPartido.equipo_visitante?.nombre }}</strong>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Cancha</label>
                <VSelectCustom
                    v-model="selectedCanchaEdit"
                    :options="canchaOptions"
                    label="label"
                    :clearable="true"
                    placeholder="Seleccionar cancha..."
                    @update:modelValue="(o) => { selectedCanchaEdit = o; editForm.partidos[0].cancha_id = o?.value ?? null }"
                />
                <InputError :message="editForm.errors['partidos.0.cancha_id']" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Fecha</label>
                    <input type="date" v-model="editForm.partidos[0].fecha" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                    <InputError :message="editForm.errors['partidos.0.fecha']" class="mt-1" />
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Hora</label>
                    <input type="time" v-model="editForm.partidos[0].hora" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                    <InputError :message="editForm.errors['partidos.0.hora']" class="mt-1" />
                </div>
            </div>

            <InputError :message="editForm.errors.partidos" class="mt-1" />

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="closeEditModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <PrimaryButton type="submit" :disabled="editForm.processing" class="px-6 py-2 text-xs">
                    Guardar Cambios
                </PrimaryButton>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-500">warning</span>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Eliminar Calendario</h3>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            Esta acción eliminará todas las jornadas y partidos del calendario. Los partidos finalizados no se pueden eliminar.
        </p>
        <div class="flex justify-end gap-2">
            <button @click="showDeleteConfirm = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                Cancelar
            </button>
            <button @click="deleteCalendar" class="px-6 py-2 bg-red-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all">
                Eliminar
            </button>
        </div>
    </div>
</div>

</AuthenticatedLayout>
</template>
