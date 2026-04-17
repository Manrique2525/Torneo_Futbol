<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    arbitro: Object,
    constantes: Object
});

const nivelesArbitro = props.constantes?.niveles_arbitro || {};

// Opciones para selects
const nivelOptions = Object.entries(nivelesArbitro).map(([key, label]) => ({
    label: label,
    value: key
}));

const disponibleOptions = [
    { label: 'Sí, disponible', value: true },
    { label: 'No disponible', value: false }
];

const form = useForm({
    nombre: props.arbitro?.nombre ?? '',
    telefono: props.arbitro?.telefono ?? '',
    email: props.arbitro?.email ?? '',
    nivel: props.arbitro?.nivel ?? 'regional',
    disponible: props.arbitro?.disponible ?? true,
    pago_por_partido: props.arbitro?.pago_por_partido ?? null,
    _method: 'put',
});

const selectedNivel = ref(nivelOptions.find(o => o.value === form.nivel));
const selectedDisponible = ref(disponibleOptions.find(o => o.value === form.disponible));

const onNivelChange = (o) => form.nivel = o?.value ?? 'regional';
const onDisponibleChange = (o) => form.disponible = o?.value ?? true;

const submit = () => {
    form.post(route('arbitros.update', props.arbitro.id));
};
</script>

<template>
<Head title="Editar Árbitro" />

<AuthenticatedLayout>
<div class="w-full">

    <!-- 🔙 VOLVER -->
    <Link
        :href="route('arbitros.index')"
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
                <span class="material-symbols-outlined text-xl">edit</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Editar Árbitro
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Modifica la información del árbitro "{{ arbitro.nombre }}"
                </p>
            </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="px-8 py-7">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                <!-- Nombre -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nombre completo *
                    </label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. Juan Pérez González"
                    >
                    <InputError :message="form.errors.nombre" class="mt-1.5" />
                </div>

                <!-- Nivel -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nivel *
                    </label>
                    <VSelectCustom
                        v-model="selectedNivel"
                        :options="nivelOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar nivel..."
                        @update:modelValue="onNivelChange"
                    />
                    <InputError :message="form.errors.nivel" class="mt-1.5" />
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Teléfono
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">phone</span>
                        </span>
                        <input
                            v-model="form.telefono"
                            type="text"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="+54 11 1234-5678"
                        >
                    </div>
                    <InputError :message="form.errors.telefono" class="mt-1.5" />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">mail</span>
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="juan.perez@ejemplo.com"
                        >
                    </div>
                    <InputError :message="form.errors.email" class="mt-1.5" />
                </div>

                <!-- Disponible -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Disponibilidad *
                    </label>
                    <VSelectCustom
                        v-model="selectedDisponible"
                        :options="disponibleOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar disponibilidad..."
                        @update:modelValue="onDisponibleChange"
                    />
                    <InputError :message="form.errors.disponible" class="mt-1.5" />
                </div>

                <!-- Pago por partido -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Pago por partido ($)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg">attach_money</span>
                        </span>
                        <input
                            v-model="form.pago_por_partido"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="0.00"
                        >
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Monto a pagar por cada partido arbitrado</p>
                    <InputError :message="form.errors.pago_por_partido" class="mt-1.5" />
                </div>

            </div>

            <!-- FOOTER -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Árbitro actualizado correctamente
                </div>
                <div v-else />

                <div class="flex gap-3">
                    <Link
                        :href="route('arbitros.index')"
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