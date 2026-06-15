<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    partido: Object,
    torneos: Array,
    canchas: Array,
    arbitros: Array,
    jornadas: Array,
    equipos: Array,
    constantes: Object,
});

const estadosPartido = props.constantes?.estados_partido || {};

const estadoOptions = Object.entries(estadosPartido).map(([key, label]) => ({ label, value: key }));

const torneoOptions = (props.torneos || []).map((t) => ({ label: t.nombre, value: t.id }));
const canchaOptions = (props.canchas || []).map((c) => ({ label: c.nombre, value: c.id }));
const arbitroOptions = (props.arbitros || []).map((a) => ({ label: a.nombre, value: a.id }));
const jornadaOptions = (props.jornadas || []).map((j) => ({ label: j.nombre, value: j.id }));
const equipoOptions = (props.equipos || []).map((e) => ({ label: e.nombre, value: e.id }));

const form = useForm({
    torneo_id: props.partido?.torneo_id ?? null,
    jornada_id: props.partido?.jornada_id ?? null,
    equipo_local_id: props.partido?.equipo_local_id ?? null,
    equipo_visitante_id: props.partido?.equipo_visitante_id ?? null,
    cancha_id: props.partido?.cancha_id ?? null,
    arbitro_id: props.partido?.arbitro_id ?? null,
    fecha: props.partido?.fecha ?? '',
    hora: props.partido?.hora ? props.partido.hora.substring(0, 5) : '',
    duracion_minutos: props.partido?.duracion_minutos ?? 90,
    estado: props.partido?.estado ?? 'programado',
    _method: 'put',
});

const selectedTorneo = ref(torneoOptions.find((o) => o.value === form.torneo_id) || null);
const selectedCancha = ref(canchaOptions.find((o) => o.value === form.cancha_id) || null);
const selectedArbitro = ref(arbitroOptions.find((o) => o.value === form.arbitro_id) || null);
const selectedJornada = ref(jornadaOptions.find((o) => o.value === form.jornada_id) || null);
const selectedEquipoLocal = ref(equipoOptions.find((o) => o.value === form.equipo_local_id) || null);
const selectedEquipoVisitante = ref(equipoOptions.find((o) => o.value === form.equipo_visitante_id) || null);
const selectedEstado = ref(estadoOptions.find((o) => o.value === form.estado) || null);

const onTorneoChange = (o) => (form.torneo_id = o?.value ?? null);
const onCanchaChange = (o) => (form.cancha_id = o?.value ?? null);
const onArbitroChange = (o) => (form.arbitro_id = o?.value ?? null);
const onJornadaChange = (o) => (form.jornada_id = o?.value ?? null);
const onEquipoLocalChange = (o) => (form.equipo_local_id = o?.value ?? null);
const onEquipoVisitanteChange = (o) => (form.equipo_visitante_id = o?.value ?? null);
const onEstadoChange = (o) => (form.estado = o?.value ?? 'programado');

const submit = () => {
    form.post(route('partidos.update', props.partido.id));
};
</script>

<template>
<Head title="Editar Partido" />

<AuthenticatedLayout>
<div class="w-full">

    <Link
        :href="route('partidos.index')"
        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
               text-slate-400 hover:text-primary focus:outline-none focus-visible:ring-2
               focus-visible:ring-primary/50 active:scale-95 transition-all mb-6 rounded-lg px-2 py-1"
    >
        <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
        Volver al listado
    </Link>

    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <div class="flex items-center gap-4 px-4 md:px-8 py-4 md:py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">edit</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Editar Partido
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Modifica la información del partido
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="px-4 md:px-8 py-5 md:py-7">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Torneo *
                    </label>
                    <VSelectCustom
                        v-model="selectedTorneo"
                        :options="torneoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar torneo..."
                        @update:modelValue="onTorneoChange"
                    />
                    <InputError :message="form.errors.torneo_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Jornada
                    </label>
                    <VSelectCustom
                        v-model="selectedJornada"
                        :options="jornadaOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar jornada..."
                        @update:modelValue="onJornadaChange"
                    />
                    <InputError :message="form.errors.jornada_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Equipo Local *
                    </label>
                    <VSelectCustom
                        v-model="selectedEquipoLocal"
                        :options="equipoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar equipo..."
                        @update:modelValue="onEquipoLocalChange"
                    />
                    <InputError :message="form.errors.equipo_local_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Equipo Visitante *
                    </label>
                    <VSelectCustom
                        v-model="selectedEquipoVisitante"
                        :options="equipoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar equipo..."
                        @update:modelValue="onEquipoVisitanteChange"
                    />
                    <InputError :message="form.errors.equipo_visitante_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Cancha
                    </label>
                    <VSelectCustom
                        v-model="selectedCancha"
                        :options="canchaOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar cancha..."
                        @update:modelValue="onCanchaChange"
                    />
                    <InputError :message="form.errors.cancha_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Árbitro
                    </label>
                    <VSelectCustom
                        v-model="selectedArbitro"
                        :options="arbitroOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar árbitro..."
                        @update:modelValue="onArbitroChange"
                    />
                    <InputError :message="form.errors.arbitro_id" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Fecha *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">calendar_today</span>
                        </span>
                        <input
                            v-model="form.fecha"
                            type="date"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                    </div>
                    <InputError :message="form.errors.fecha" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Hora *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">schedule</span>
                        </span>
                        <input
                            v-model="form.hora"
                            type="time"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                    </div>
                    <InputError :message="form.errors.hora" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Duración (minutos) *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">timer</span>
                        </span>
                        <input
                            v-model="form.duracion_minutos"
                            type="number"
                            min="15"
                            max="300"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="90"
                        >
                    </div>
                    <InputError :message="form.errors.duracion_minutos" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Estado *
                    </label>
                    <VSelectCustom
                        v-model="selectedEstado"
                        :options="estadoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar estado..."
                        @update:modelValue="onEstadoChange"
                    />
                    <InputError :message="form.errors.estado" class="mt-1.5" />
                </div>

            </div>

            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Partido actualizado correctamente
                </div>
                <div v-else class="hidden sm:block" />

                <div class="flex gap-3">
                    <Link
                        :href="route('partidos.index')"
                        class="px-6 py-3 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400
                               font-bold uppercase tracking-wider text-sm hover:bg-slate-200 dark:hover:bg-white/10
                               transition-all"
                    >
                        Cancelar
                    </Link>

                    <PrimaryButton
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                    >
                        Guardar Cambios
                    </PrimaryButton>
                </div>
            </div>

        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>
