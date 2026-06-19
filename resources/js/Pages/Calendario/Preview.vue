<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    torneo: Object,
    preview: Object,
    constantes: Object,
});

const confirming = ref(false);
const activeTab = ref('regular');
const showEditModal = ref(false);
const selectedPartido = ref(null);
const selectedContext = ref(null);
const selectedCancha = ref(null);

const faseLabels = props.constantes?.fases_partido || {};

const ajustes = ref({
    jornadas: {},
    partidos: {},
    bracket: {},
});

const editForm = useForm({
    cancha_id: null,
    fecha: null,
    hora: null,
});

const canchaOptions = computed(() =>
    (props.preview?.canchas_disponibles || []).map(c => ({ label: c.nombre, value: c.id }))
);

const tienePlayoff = computed(() => {
    return props.preview.bracket && props.preview.bracket.length > 0;
});

const totalPartidos = computed(() => {
    let count = 0;
    if (props.preview.jornadas) {
        props.preview.jornadas.forEach(j => {
            count += j.partidos?.length || 0;
        });
    }
    if (props.preview.bracket) {
        props.preview.bracket.forEach(fase => {
            count += fase.partidos?.length || 0;
        });
    }
    return count;
});

const confirmCalendar = () => {
    confirming.value = true;
    router.post(route('calendario.store', props.torneo.id), {
        ajustes: ajustes.value
    }, {
        onFinish: () => {
            confirming.value = false;
        }
    });
};

const getTeamName = (team) => {
    if (!team) return 'TBD';
    return team.equipo?.nombre || `Equipo #${team.team_id || team.id}`;
};

const getAjuste = (context) => {
    if (context.tipo === 'bracket') {
        return ajustes.value.bracket[context.fase]?.[context.idx] || {};
    }
    return ajustes.value.partidos[context.jornadaNumero]?.[context.idx] || {};
};

const getCanchaLabel = (context) => {
    const ajuste = getAjuste(context);
    if (!ajuste.cancha_id) return null;
    const cancha = canchaOptions.value.find(c => c.value === ajuste.cancha_id);
    return cancha?.label || null;
};

const horaBase = computed(() => props.preview?.torneo?.hora_inicio ?? '12:00');

const openEditModal = (partido, context) => {
    selectedPartido.value = partido;
    selectedContext.value = context;
    const ajuste = getAjuste(context);
    editForm.cancha_id = ajuste.cancha_id ?? null;
    editForm.fecha = ajuste.fecha ?? (context.tipo === 'regular' ? context.jornadaFecha : null);
    editForm.hora = ajuste.hora ?? horaBase.value;
    selectedCancha.value = editForm.cancha_id
        ? canchaOptions.value.find(c => c.value === editForm.cancha_id) ?? null
        : null;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedPartido.value = null;
    selectedContext.value = null;
    selectedCancha.value = null;
    editForm.reset();
};

const submitEdit = () => {
    const ctx = selectedContext.value;
    if (!ctx) return;

    if (ctx.tipo === 'bracket') {
        if (!ajustes.value.bracket[ctx.fase]) ajustes.value.bracket[ctx.fase] = {};
        ajustes.value.bracket[ctx.fase][ctx.idx] = {
            cancha_id: editForm.cancha_id,
            fecha: editForm.fecha,
            hora: editForm.hora,
        };
    } else {
        if (!ajustes.value.partidos[ctx.jornadaNumero]) ajustes.value.partidos[ctx.jornadaNumero] = {};
        ajustes.value.partidos[ctx.jornadaNumero][ctx.idx] = {
            cancha_id: editForm.cancha_id,
            fecha: editForm.fecha,
            hora: editForm.hora,
        };
    }

    closeEditModal();
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
<Head title="Preview del Calendario" />

<AuthenticatedLayout>
<div class="w-full">

    <Link
        :href="route('calendario.show', torneo.id)"
        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
               text-slate-400 hover:text-primary focus:outline-none focus-visible:ring-2
               focus-visible:ring-primary/50 active:scale-95 transition-all mb-6 rounded-lg px-2 py-1"
    >
        <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
        Volver al calendario
    </Link>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <div class="lg:col-span-3 space-y-6">

            <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

                <div class="px-4 md:px-8 py-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 flex items-center justify-center text-white shadow-lg shadow-primary/30">
                            <span class="material-symbols-outlined text-2xl">preview</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                Preview del Calendario
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">{{ torneo.nombre }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-3">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tipo</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white capitalize">{{ torneo.tipo }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-3">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Equipos</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white">{{ preview.total_equipos }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-3">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Jornadas</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white">{{ preview.jornadas?.length || 0 }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-xl p-3">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Partidos</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white">{{ totalPartidos }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <span v-if="torneo.ida_y_vuelta" class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-bold">Ida y Vuelta</span>
                        <span v-if="torneo.tiene_playoff" class="text-[10px] bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full font-bold">Playoff {{ torneo.playoff_equipos }} equipos</span>
                        <span v-if="torneo.formato_relampago" class="text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 px-2 py-0.5 rounded-full font-bold capitalize">{{ torneo.formato_relampago }}</span>
                    </div>
                </div>

                <div class="px-4 md:px-8 py-5">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-blue-700 dark:text-blue-400 text-xs mb-6">
                        <span class="material-symbols-outlined !text-sm align-middle mr-1">info</span>
                        Revisa los enfrentamientos generados. Podrás ajustar canchas, horarios y árbitros después de confirmar el calendario.
                    </div>

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
                        <div v-for="jornada in preview.jornadas" :key="jornada.numero" class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-slate-50 to-slate-100/50 dark:from-white/5 dark:to-white/10">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary !text-lg">event</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Jornada {{ jornada.numero }}</h4>
                                        <p class="text-[11px] text-slate-500">{{ jornada.dia_semana }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-slate-800 dark:text-white">{{ jornada.fecha }}</div>
                                    <div class="text-[10px] text-slate-400">{{ jornada.partidos?.length || 0 }} partidos</div>
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                <div v-for="(partido, idx) in jornada.partidos" :key="idx" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="text-right flex-1">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ getTeamName(partido.local) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 px-4">
                                            <span class="text-xs font-black text-slate-400 uppercase">vs</span>
                                        </div>
                                        <div class="text-left flex-1">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ getTeamName(partido.visitante) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                        <span>{{ getAjuste({ tipo: 'regular', jornadaNumero: jornada.numero, idx }).fecha || jornada.fecha }}</span>
                                        <span>{{ getAjuste({ tipo: 'regular', jornadaNumero: jornada.numero, idx }).hora || props.preview?.torneo?.hora_inicio || '12:00' }}</span>
                                        <span v-if="getCanchaLabel({ tipo: 'regular', jornadaNumero: jornada.numero, idx })" class="max-w-[80px] truncate">
                                            {{ getCanchaLabel({ tipo: 'regular', jornadaNumero: jornada.numero, idx }) }}
                                        </span>
                                        <button
                                            @click="openEditModal(partido, { tipo: 'regular', jornadaNumero: jornada.numero, jornadaFecha: jornada.fecha, idx })"
                                            class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                                            title="Asignar cancha, fecha y hora"
                                        >
                                            <span class="material-symbols-outlined !text-base">edit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="activeTab === 'playoff'" class="space-y-6">
                        <div v-for="fase in preview.bracket" :key="fase.fase" class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                            <div class="px-4 py-3 bg-gradient-to-r from-amber-50 to-amber-100/50 dark:from-amber-900/10 dark:to-amber-900/5">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-amber-500 !text-lg">emoji_events</span>
                                    {{ faseLabels[fase.fase] || fase.fase }}
                                </h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
                                <div v-for="(partido, idx) in fase.partidos" :key="idx" class="border border-slate-100 dark:border-slate-700 rounded-xl p-3 bg-slate-50/50 dark:bg-white/5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] text-slate-400 font-bold">{{ partido.llave }}</span>
                                        <div class="flex items-center gap-2">
                                            <span v-if="partido.es_vuelta" class="text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 px-2 py-0.5 rounded-full font-bold">Vuelta</span>
                                            <button
                                                @click="openEditModal(partido, { tipo: 'bracket', fase: fase.fase, idx })"
                                                class="p-1 text-slate-400 hover:text-primary transition-colors"
                                                title="Asignar cancha, fecha y hora"
                                            >
                                                <span class="material-symbols-outlined !text-base">edit</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ getTeamName(partido.local) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-200 dark:border-slate-700"></div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">
                                                {{ getTeamName(partido.visitante) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex items-center gap-2 text-[10px] text-slate-400">
                                        <span>{{ getAjuste({ tipo: 'bracket', fase: fase.fase, idx }).fecha || 'Sin fecha' }}</span>
                                        <span>{{ getAjuste({ tipo: 'bracket', fase: fase.fase, idx }).hora || props.preview?.torneo?.hora_inicio || '' }}</span>
                                        <span v-if="getCanchaLabel({ tipo: 'bracket', fase: fase.fase, idx })" class="truncate">
                                            {{ getCanchaLabel({ tipo: 'bracket', fase: fase.fase, idx }) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-4">
                <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white mb-4">
                        Resumen
                    </h3>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Tipo de torneo</span>
                            <span class="font-bold text-slate-800 dark:text-white capitalize">{{ torneo.tipo }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Total equipos</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ preview.total_equipos }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Jornadas</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ preview.jornadas?.length || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Total partidos</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ totalPartidos }}</span>
                        </div>
                        <div v-if="torneo.ida_y_vuelta" class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Formato</span>
                            <span class="font-bold text-primary">Ida y Vuelta</span>
                        </div>
                        <div v-if="torneo.tiene_playoff" class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Playoff</span>
                            <span class="font-bold text-amber-600">{{ torneo.playoff_equipos }} equipos</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Hora base</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ preview.torneo?.hora_inicio || '12:00' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Duración</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ preview.torneo?.duracion_minutos || 90 }} min</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <PrimaryButton
                            @click="confirmCalendar"
                            :disabled="confirming"
                            class="w-full px-6 py-3 text-xs justify-center"
                        >
                            <span class="material-symbols-outlined !text-base mr-2">{{ confirming ? 'hourglass_empty' : 'check_circle' }}</span>
                            {{ confirming ? 'Confirmando...' : 'Confirmar Calendario' }}
                        </PrimaryButton>

                        <Link
                            :href="route('calendario.show', torneo.id)"
                            class="flex items-center justify-center w-full px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all"
                        >
                            <span class="material-symbols-outlined !text-base mr-2">close</span>
                            Cancelar
                        </Link>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-2xl p-4">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-blue-500 !text-lg mt-0.5">lightbulb</span>
                        <div class="text-[11px] text-blue-700 dark:text-blue-400">
                            <p class="font-bold mb-1">Próximos pasos</p>
                            <p class="leading-relaxed">Después de confirmar, podrás asignar canchas, horarios y árbitros a cada partido desde la vista del calendario.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Partido Modal -->
<div v-if="showEditModal && selectedPartido" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-2xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                Asignar Cancha, Fecha y Hora
            </h3>
            <button @click="closeEditModal" class="p-2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="submitEdit" class="p-6 space-y-4">
            <div class="p-3 bg-slate-50 dark:bg-white/5 rounded-xl text-xs text-slate-600 dark:text-slate-400">
                <strong>{{ getTeamName(selectedPartido.local) }}</strong> vs <strong>{{ getTeamName(selectedPartido.visitante) }}</strong>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Cancha</label>
                <VSelectCustom
                    v-model="selectedCancha"
                    :options="canchaOptions"
                    label="label"
                    :clearable="true"
                    placeholder="Seleccionar cancha..."
                    @update:modelValue="(o) => { selectedCancha = o; editForm.cancha_id = o?.value ?? null }"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Fecha</label>
                    <input type="date" v-model="editForm.fecha" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Hora</label>
                    <input type="time" v-model="editForm.hora" class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2 px-3 text-sm text-slate-800 dark:text-white outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="closeEditModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <PrimaryButton type="submit" :disabled="editForm.processing" class="px-6 py-2 text-xs">
                    Aceptar
                </PrimaryButton>
            </div>
        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>
