<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    torneo: Object,
    constantes: Object,
    isEditing: Boolean
});

const constantes = props.constantes || {};

const tipoOptions = Object.entries(constantes.tipos_torneo || {}).map(([key, label]) => ({ label, value: key }));
const categoriaOptions = Object.entries(constantes.categorias || {}).map(([key, label]) => ({ label, value: key }));
const ramaOptions = Object.entries(constantes.ramas || {}).map(([key, label]) => ({ label, value: key }));
const estadoOptions = Object.entries(constantes.estados_torneo || {
    activo: 'Activo',
    finalizado: 'Finalizado',
    cancelado: 'Cancelado'
}).map(([key, label]) => ({ label, value: key }));

const selectedTipo = ref(tipoOptions.find(o => o.value === props.torneo?.tipo) ?? null);
const selectedCategoria = ref(categoriaOptions.find(o => o.value === props.torneo?.categoria) ?? null);
const selectedRama = ref(ramaOptions.find(o => o.value === props.torneo?.rama) ?? null);
const selectedEstado = ref(estadoOptions.find(o => o.value === (props.torneo?.estado ?? 'activo')) ?? null);

const form = useForm({
    nombre: props.torneo?.nombre ?? '',
    tipo: props.torneo?.tipo ?? '',
    categoria: props.torneo?.categoria ?? '',
    rama: props.torneo?.rama ?? '',
    fecha_inicio: props.torneo?.fecha_inicio ?? '',
    fecha_fin: props.torneo?.fecha_fin ?? '',
    estado: props.torneo?.estado ?? 'activo',
    reglas: props.torneo?.reglas ?? '',
});

const onTipoChange = (o) => form.tipo = o?.value ?? '';
const onCategoriaChange = (o) => form.categoria = o?.value ?? '';
const onRamaChange = (o) => form.rama = o?.value ?? '';
const onEstadoChange = (o) => form.estado = o?.value ?? 'activo';

const submit = () => {
    if (props.isEditing) {
        form.put(route('torneos.update', props.torneo.id));
    } else {
        form.post(route('torneos.store'));
    }
};
</script>

<template>
<Head :title="isEditing ? 'Editar Torneo' : 'Nuevo Torneo'" />

<AuthenticatedLayout>
<div class="w-full">

    <!-- 🔙 VOLVER -->
    <Link
        :href="route('torneos.index')"
        class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
               text-slate-400 hover:text-primary focus:outline-none focus-visible:ring-2
               focus-visible:ring-primary/50 active:scale-95 transition-all mb-6 rounded-lg px-2 py-1"
    >
        <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
        Volver al listado
    </Link>

    <!-- 🧾 CARD -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">
                    {{ isEditing ? 'edit' : 'emoji_events' }}
                </span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    {{ isEditing ? 'Editar Torneo' : 'Nuevo Torneo' }}
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    {{ isEditing
                        ? 'Modifica la información del torneo.'
                        : 'Completa la información para registrar un nuevo torneo.'
                    }}
                </p>
            </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="px-8 py-7">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                <!-- Nombre -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nombre del Torneo
                    </label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. Liga Municipal 2026"
                    >
                    <InputError :message="form.errors.nombre" class="mt-1.5" />
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Tipo
                    </label>
                    <VSelectCustom
                        v-model="selectedTipo"
                        :options="tipoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar..."
                        @update:modelValue="onTipoChange"
                    />
                    <InputError :message="form.errors.tipo" class="mt-1.5" />
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Categoría
                    </label>
                    <VSelectCustom
                        v-model="selectedCategoria"
                        :options="categoriaOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar..."
                        @update:modelValue="onCategoriaChange"
                    />
                    <InputError :message="form.errors.categoria" class="mt-1.5" />
                </div>

                <!-- Rama -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Rama
                    </label>
                    <VSelectCustom
                        v-model="selectedRama"
                        :options="ramaOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar..."
                        @update:modelValue="onRamaChange"
                    />
                    <InputError :message="form.errors.rama" class="mt-1.5" />
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Estado
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

                <!-- Fecha Inicio -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Fecha Inicio
                    </label>
                    <input
                        type="date"
                        v-model="form.fecha_inicio"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               transition-all outline-none"
                    >
                    <InputError :message="form.errors.fecha_inicio" class="mt-1.5" />
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Fecha Fin
                    </label>
                    <input
                        type="date"
                        v-model="form.fecha_fin"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               transition-all outline-none"
                    >
                    <InputError :message="form.errors.fecha_fin" class="mt-1.5" />
                </div>

                <!-- Reglas -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Reglas
                    </label>
                    <textarea
                        v-model="form.reglas"
                        rows="4"
                        placeholder="Reglas del torneo..."
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none resize-none"
                    ></textarea>
                    <InputError :message="form.errors.reglas" class="mt-1.5" />
                </div>

            </div>

            <!-- FOOTER -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    {{ isEditing ? 'Torneo actualizado correctamente' : 'Torneo creado correctamente' }}
                </div>
                <div v-else />

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing"
                    class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                >
                    {{ isEditing ? 'Guardar Cambios' : 'Crear Torneo' }}
                </PrimaryButton>
            </div>

        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>