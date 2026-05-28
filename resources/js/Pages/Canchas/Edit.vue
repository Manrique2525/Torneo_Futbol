<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    cancha: Object,
    disponibilidades: Array,
    constantes: Object,
});

const tiposCancha = props.constantes?.tipos_cancha || {};
const estadosCancha = props.constantes?.estados_cancha || {};

const diasLabels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const tipoOptions = Object.entries(tiposCancha).map(([key, label]) => ({ label, value: key }));
const estadoOptions = Object.entries(estadosCancha).map(([key, label]) => ({ label, value: key }));

const existingDisponibilidad = (diaSemana) => {
    return (props.disponibilidades || []).find((d) => d.dia_semana === diaSemana);
};

const disponibilidadDias = ref(diasLabels.map((label, index) => {
    const existing = existingDisponibilidad(index);
    return {
        dia_semana: index,
        label,
        enabled: !!existing,
        hora_inicio: existing?.hora_inicio || '08:00',
        hora_fin: existing?.hora_fin || '22:00',
    };
}));

const form = useForm({
    nombre: props.cancha?.nombre ?? '',
    direccion: props.cancha?.direccion ?? '',
    tipo: props.cancha?.tipo ?? 'futbol-11',
    capacidad: props.cancha?.capacidad ?? null,
    latitud: props.cancha?.latitud ?? null,
    longitud: props.cancha?.longitud ?? null,
    estado: props.cancha?.estado ?? 'activo',
    disponibilidades: [],
    _method: 'put',
});

const selectedTipo = ref(tipoOptions.find((o) => o.value === form.tipo));
const selectedEstado = ref(estadoOptions.find((o) => o.value === form.estado));

const onTipoChange = (o) => (form.tipo = o?.value ?? 'futbol-11');
const onEstadoChange = (o) => (form.estado = o?.value ?? 'activo');

const submit = () => {
    form.disponibilidades = disponibilidadDias.value
        .filter((d) => d.enabled)
        .map((d) => ({
            dia_semana: d.dia_semana,
            hora_inicio: d.hora_inicio,
            hora_fin: d.hora_fin,
        }));

    form.post(route('canchas.update', props.cancha.id));
};
</script>

<template>
<Head title="Editar Cancha" />

<AuthenticatedLayout>
<div class="w-full">

    <Link
        :href="route('canchas.index')"
        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
               text-slate-400 hover:text-primary focus:outline-none focus-visible:ring-2
               focus-visible:ring-primary/50 active:scale-95 transition-all mb-6 rounded-lg px-2 py-1"
    >
        <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
        Volver al listado
    </Link>

    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">edit</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Editar Cancha
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Modifica la información de la cancha "{{ cancha.nombre }}"
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="px-8 py-7">

            <!-- DATOS GENERALES -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nombre *
                    </label>
                    <input v-model="form.nombre" type="text"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. Cancha Principal">
                    <InputError :message="form.errors.nombre" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Tipo *
                    </label>
                    <VSelectCustom v-model="selectedTipo" :options="tipoOptions" label="label"
                        :clearable="false" placeholder="Seleccionar tipo..."
                        @update:modelValue="onTipoChange" />
                    <InputError :message="form.errors.tipo" class="mt-1.5" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Dirección
                    </label>
                    <div class="relative">
                        <span class="absolute top-3 left-3 flex items-start text-slate-400">
                            <span class="material-symbols-outlined text-lg">location_on</span>
                        </span>
                        <textarea v-model="form.direccion" rows="2"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none resize-none"
                            placeholder="Ej. Av. Principal 123, Col. Centro"></textarea>
                    </div>
                    <InputError :message="form.errors.direccion" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Capacidad (personas)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">group</span>
                        </span>
                        <input v-model="form.capacidad" type="number" min="0"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="0">
                    </div>
                    <InputError :message="form.errors.capacidad" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Estado *
                    </label>
                    <VSelectCustom v-model="selectedEstado" :options="estadoOptions" label="label"
                        :clearable="false" placeholder="Seleccionar estado..."
                        @update:modelValue="onEstadoChange" />
                    <InputError :message="form.errors.estado" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Latitud
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">pin_drop</span>
                        </span>
                        <input v-model="form.latitud" type="number" step="0.0000001"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="Ej. 19.4326">
                    </div>
                    <InputError :message="form.errors.latitud" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Longitud
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">pin_drop</span>
                        </span>
                        <input v-model="form.longitud" type="number" step="0.0000001"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="Ej. -99.1332">
                    </div>
                    <InputError :message="form.errors.longitud" class="mt-1.5" />
                </div>
            </div>

            <!-- DISPONIBILIDAD -->
            <div class="mb-7 border-t border-slate-100 dark:border-slate-800 pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-lg text-primary">schedule</span>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-800 dark:text-white">
                        Disponibilidad Semanal
                    </h3>
                </div>
                <p class="text-[10px] text-slate-400 mb-4">Configura los días y horarios en que la cancha está disponible. Solo los días activados se guardarán.</p>

                <div class="space-y-3">
                    <div v-for="(dia, i) in disponibilidadDias" :key="i"
                        class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-white/5 rounded-2xl">
                        <label class="flex items-center gap-2 min-w-[120px] cursor-pointer">
                            <input type="checkbox" v-model="dia.enabled"
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30">
                            <span class="text-xs font-bold uppercase text-slate-600 dark:text-slate-400">
                                {{ dia.label }}
                            </span>
                        </label>

                        <template v-if="dia.enabled">
                            <input v-model="dia.hora_inicio" type="time"
                                class="w-28 bg-white dark:bg-white/10 border border-slate-200 dark:border-slate-700
                                       rounded-xl py-2 px-3 text-xs text-slate-800 dark:text-white
                                       focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                            <span class="text-xs text-slate-400">a</span>
                            <input v-model="dia.hora_fin" type="time"
                                class="w-28 bg-white dark:bg-white/10 border border-slate-200 dark:border-slate-700
                                       rounded-xl py-2 px-3 text-xs text-slate-800 dark:text-white
                                       focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                            <button type="button" @click="dia.enabled = false"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all">
                                <span class="material-symbols-outlined !text-base">close</span>
                            </button>
                        </template>
                        <span v-else class="text-[10px] text-slate-300 dark:text-slate-600 uppercase tracking-wider">
                            No disponible
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Cancha actualizada correctamente
                </div>
                <div v-else />

                <div class="flex gap-3">
                    <Link
                        :href="route('canchas.index')"
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
