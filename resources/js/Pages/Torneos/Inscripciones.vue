<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useCan } from '@/Shared/Composables/useCan';

const props = defineProps({
    torneo: Object,
    inscripciones: Object,
    grupos: Array,
    equiposDisponibles: Array,
    cupo: Object,
    filters: Object,
    constantes: Object,
    can: Object,
    flash: Object,
});

const { can } = useCan();
const estados = props.constantes?.estados_inscripcion || {};

const searchQuery = ref(props.filters?.search || '');
const filterEstado = ref(props.filters?.estado || 'todos');

const estadoOptions = [
    { label: 'Todos los estados', value: 'todos' },
    ...Object.entries(estados).map(([value, label]) => ({ label, value })),
];

const equipoOptions = computed(() =>
    (props.equiposDisponibles || []).map((e) => ({ label: e.name, value: e.id }))
);

const grupoOptions = computed(() =>
    (props.grupos || []).map((g) => ({ label: g.nombre, value: g.id }))
);

const debounce = (fn, delay) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
};

const search = debounce(() => {
    router.get(
        route('torneos.equipos.index', props.torneo.id),
        {
            search: searchQuery.value || undefined,
            estado: filterEstado.value === 'todos' ? undefined : filterEstado.value,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([searchQuery, filterEstado], search);

const inscribirModal = ref(false);
const rechazarModal = ref({ show: false, inscripcion: null });

const formInscribir = useForm({
    team_id: null,
    torneo_grupo_id: null,
    seed: null,
    notas: '',
});

const formRechazar = useForm({
    motivo_rechazo: '',
});

const submitInscribir = () => {
    formInscribir.post(route('torneos.equipos.store', props.torneo.id), {
        onSuccess: () => {
            inscribirModal.value = false;
            formInscribir.reset();
        },
    });
};

const aprobar = (inscripcion) => {
    router.post(route('torneos.equipos.aprobar', [props.torneo.id, inscripcion.id]));
};

const openRechazar = (inscripcion) => {
    rechazarModal.value = { show: true, inscripcion };
    formRechazar.reset();
};

const submitRechazar = () => {
    formRechazar.post(
        route('torneos.equipos.rechazar', [props.torneo.id, rechazarModal.value.inscripcion.id]),
        {
            onSuccess: () => {
                rechazarModal.value = { show: false, inscripcion: null };
            },
        }
    );
};

const asignarGrupo = (inscripcion, grupoId) => {
    router.patch(route('torneos.equipos.asignar-grupo', [props.torneo.id, inscripcion.id]), {
        torneo_grupo_id: grupoId,
    });
};

const asignarSeed = (inscripcion, seed) => {
    router.patch(route('torneos.equipos.asignar-seed', [props.torneo.id, inscripcion.id]), {
        seed: seed === '' || seed === null ? null : Number(seed),
    });
};

const getEstadoBadge = (estado) => {
    const colors = {
        pendiente: 'bg-amber-500/10 text-amber-600 border-amber-500/20',
        aprobado: 'bg-green-500/10 text-green-600 border-green-500/20',
        rechazado: 'bg-red-500/10 text-red-600 border-red-500/20',
        retirado: 'bg-slate-500/10 text-slate-600 border-slate-500/20',
        descalificado: 'bg-purple-500/10 text-purple-600 border-purple-500/20',
    };
    return colors[estado] || 'bg-slate-100 text-slate-500 border-slate-200';
};

const cupoLabel = computed(() => {
    if (!props.cupo?.max) return `${props.cupo?.ocupados ?? 0} equipos inscritos`;
    return `${props.cupo.ocupados} / ${props.cupo.max} cupos`;
});

const puedeGestionar = computed(
    () => props.can?.update || can('tournaments.update')
);
const puedeInscribir = computed(
    () => props.can?.create || can('tournaments.update') || can('teams.update_own')
);
</script>

<template>
    <Head :title="`Equipos — ${torneo.nombre}`" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <Link
                        :href="route('torneos.index')"
                        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary mb-4"
                    >
                        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
                        Torneos
                    </Link>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Equipos <span class="text-primary">{{ torneo.nombre }}</span>
                    </h2>
                    <p class="text-slate-500 text-sm mt-2 font-medium">
                        {{ cupoLabel }}
                        <span v-if="!torneo.inscripcion_abierta" class="ml-2 text-amber-600 font-bold">· Inscripciones cerradas</span>
                    </p>
                </div>

                <button
                    v-if="puedeInscribir && equiposDisponibles?.length"
                    type="button"
                    @click="inscribirModal = true"
                    class="bg-primary hover:bg-green-600 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/20 transition-all"
                >
                    Inscribir equipo
                </button>
            </div>

            <div
                v-if="flash?.success"
                class="bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl flex items-center gap-3"
            >
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
            </div>

            <div class="flex flex-col lg:flex-row gap-4">
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Buscar equipo..."
                    class="flex-1 rounded-2xl border-slate-200 dark:border-slate-700 dark:bg-black/20 dark:text-white"
                />
                <VSelectCustom
                    v-model="filterEstado"
                    :options="estadoOptions"
                    label="label"
                    :reduce="(opt) => opt.value"
                    :clearable="false"
                    class="w-full lg:w-64"
                />
            </div>

            <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Equipo</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Grupo</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Seed</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Fair play</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                                <th v-if="puedeGestionar" class="p-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            <tr
                                v-for="ins in inscripciones?.data || []"
                                :key="ins.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-white/5"
                            >
                                <td class="p-6">
                                    <span class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ ins.equipo?.name }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <select
                                        v-if="puedeGestionar && ins.estado === 'aprobado'"
                                        :value="ins.grupo?.id"
                                        class="text-sm rounded-xl border-slate-200 dark:border-slate-700 dark:bg-black/20 dark:text-white"
                                        @change="asignarGrupo(ins, Number($event.target.value))"
                                    >
                                        <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
                                    </select>
                                    <span v-else class="text-sm">{{ ins.grupo?.nombre || '—' }}</span>
                                </td>
                                <td class="p-6 text-center">
                                    <input
                                        v-if="puedeGestionar"
                                        type="number"
                                        min="1"
                                        max="999"
                                        :value="ins.seed ?? ''"
                                        placeholder="—"
                                        class="w-20 mx-auto text-center text-sm rounded-xl border-slate-200 dark:border-slate-700 dark:bg-black/20 dark:text-white"
                                        @change="asignarSeed(ins, $event.target.value)"
                                    />
                                    <span v-else class="text-sm">{{ ins.seed ?? '—' }}</span>
                                </td>
                                <td class="p-6 text-center text-sm font-bold text-primary">
                                    {{ ins.fair_play_points }}
                                </td>
                                <td class="p-6 text-center">
                                    <span
                                        :class="['px-3 py-1 rounded-xl text-[10px] font-black uppercase border-b-2', getEstadoBadge(ins.estado)]"
                                    >
                                        {{ estados[ins.estado] || ins.estado }}
                                    </span>
                                </td>
                                <td v-if="puedeGestionar" class="p-6 text-right">
                                    <div v-if="ins.estado === 'pendiente'" class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-3 py-2 rounded-xl bg-green-500/10 text-green-600 text-[10px] font-black uppercase hover:bg-green-600 hover:text-white transition-all"
                                            @click="aprobar(ins)"
                                        >
                                            Aprobar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-2 rounded-xl bg-red-500/10 text-red-600 text-[10px] font-black uppercase hover:bg-red-600 hover:text-white transition-all"
                                            @click="openRechazar(ins)"
                                        >
                                            Rechazar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!inscripciones?.data?.length">
                                <td :colspan="puedeGestionar ? 6 : 5" class="text-center py-12 text-slate-400">
                                    No hay equipos inscritos en este torneo
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
                    <Pagination :links="inscripciones.links" />
                </div>
            </div>
        </div>

        <Modal :show="inscribirModal" maxWidth="md" @close="inscribirModal = false">
            <div class="p-8 space-y-4">
                <h3 class="text-xl font-black uppercase tracking-tighter">Inscribir equipo</h3>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400">Equipo</label>
                    <VSelectCustom
                        v-model="formInscribir.team_id"
                        :options="equipoOptions"
                        label="label"
                        :reduce="(opt) => opt.value"
                        placeholder="Seleccionar equipo"
                    />
                </div>
                <div v-if="grupoOptions.length">
                    <label class="text-[10px] font-black uppercase text-slate-400">Grupo</label>
                    <VSelectCustom
                        v-model="formInscribir.torneo_grupo_id"
                        :options="grupoOptions"
                        label="label"
                        :reduce="(opt) => opt.value"
                        placeholder="Opcional"
                    />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400">Seed (opcional)</label>
                    <input
                        v-model="formInscribir.seed"
                        type="number"
                        min="1"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-700 dark:bg-black/20 dark:text-white"
                    />
                </div>
                <PrimaryButton class="w-full justify-center" :disabled="formInscribir.processing" @click="submitInscribir">
                    Registrar inscripción
                </PrimaryButton>
            </div>
        </Modal>

        <Modal :show="rechazarModal.show" maxWidth="md" @close="rechazarModal.show = false">
            <div class="p-8 space-y-4">
                <h3 class="text-xl font-black uppercase tracking-tighter">Rechazar inscripción</h3>
                <textarea
                    v-model="formRechazar.motivo_rechazo"
                    rows="3"
                    placeholder="Motivo (opcional)"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-700 dark:bg-black/20 dark:text-white"
                />
                <PrimaryButton
                    class="w-full justify-center !bg-red-600 hover:!bg-red-700"
                    :disabled="formRechazar.processing"
                    @click="submitRechazar"
                >
                    Confirmar rechazo
                </PrimaryButton>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
