<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    jornada: Object,
    torneos: Array,
    constantes: Object,
});

const estadosJornada = props.constantes?.estados_jornada || {};

const estadoOptions = Object.entries(estadosJornada).map(([key, label]) => ({
    label,
    value: key,
}));

const torneoOptions = (props.torneos || []).map((t) => ({
    label: t.nombre,
    value: t.id,
}));

const form = useForm({
    torneo_id: props.jornada?.torneo_id ?? null,
    nombre: props.jornada?.nombre ?? '',
    numero: props.jornada?.numero ?? 1,
    fecha_inicio: props.jornada?.fecha_inicio ?? '',
    fecha_fin: props.jornada?.fecha_fin ?? '',
    estado: props.jornada?.estado ?? 'borrador',
    descripcion: props.jornada?.descripcion ?? '',
    _method: 'put',
});

const selectedTorneo = ref(torneoOptions.find((o) => o.value === form.torneo_id) || null);
const selectedEstado = ref(estadoOptions.find((o) => o.value === form.estado) || null);

const onTorneoChange = (o) => (form.torneo_id = o?.value ?? null);
const onEstadoChange = (o) => (form.estado = o?.value ?? 'borrador');

const submit = () => {
    form.post(route('jornadas.update', props.jornada.id));
};
</script>

<template>
<Head title="Editar Jornada" />

<AuthenticatedLayout>
<div class="w-full">

    <Link
        :href="route('jornadas.index')"
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
                    Editar Jornada
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Modifica la información de la jornada "{{ jornada.nombre }}"
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
                        Número *
                    </label>
                    <input
                        v-model="form.numero"
                        type="number"
                        min="1"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. 1"
                    >
                    <InputError :message="form.errors.numero" class="mt-1.5" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nombre *
                    </label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. Jornada 1 — Fase de Grupos"
                    >
                    <InputError :message="form.errors.nombre" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Fecha de inicio *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">calendar_today</span>
                        </span>
                        <input
                            v-model="form.fecha_inicio"
                            type="date"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                    </div>
                    <InputError :message="form.errors.fecha_inicio" class="mt-1.5" />
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Fecha de fin
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">event</span>
                        </span>
                        <input
                            v-model="form.fecha_fin"
                            type="date"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                    </div>
                    <InputError :message="form.errors.fecha_fin" class="mt-1.5" />
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

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Descripción
                    </label>
                    <div class="relative">
                        <span class="absolute top-3 left-3 flex items-start text-slate-400">
                            <span class="material-symbols-outlined text-lg">description</span>
                        </span>
                        <textarea
                            v-model="form.descripcion"
                            rows="2"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none resize-none"
                            placeholder="Notas adicionales sobre la jornada"
                        ></textarea>
                    </div>
                    <InputError :message="form.errors.descripcion" class="mt-1.5" />
                </div>

            </div>

            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Jornada actualizada correctamente
                </div>
                <div v-else class="hidden sm:block" />

                <div class="flex gap-3">
                    <Link
                        :href="route('jornadas.index')"
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
