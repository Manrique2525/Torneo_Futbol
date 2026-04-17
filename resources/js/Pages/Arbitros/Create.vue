<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    planInfo: Object,
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
    nombre: '',
    telefono: '',
    email: '',
    nivel: 'regional',
    disponible: true,
    pago_por_partido: null,
});

const selectedNivel = ref(nivelOptions.find(o => o.value === 'regional'));
const selectedDisponible = ref(disponibleOptions.find(o => o.value === true));

const onNivelChange = (o) => form.nivel = o?.value ?? 'regional';
const onDisponibleChange = (o) => form.disponible = o?.value ?? true;

const submit = () => {
    form.post(route('arbitros.store'));
};
</script>

<template>
<Head title="Nuevo Árbitro" />

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

    <!-- ⚠️ ALERTA DE LÍMITE DEL PLAN -->
    <div v-if="!planInfo?.canCreate" 
        class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 flex items-center gap-3">
        <span class="material-symbols-outlined text-amber-600">warning</span>
        <div class="flex-1">
            <p class="text-sm font-bold text-amber-700 dark:text-amber-400">
                Límite de árbitros alcanzado
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-500">
                {{ planInfo?.isUnlimited 
                    ? 'Tu plan no permite crear más árbitros' 
                    : `Tu plan actual permite un máximo de ${planInfo.limit} árbitros. Contacta con soporte para aumentar el límite.`
                }}
            </p>
        </div>
    </div>

    <!-- 🧾 CARD -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">sports_score</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Nuevo Árbitro
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Completa la información para registrar un nuevo árbitro.
                    <span v-if="planInfo?.remaining !== null && planInfo.remaining > 0" 
                          class="text-primary font-bold">
                        Te quedan {{ planInfo.remaining }} de {{ planInfo.limit === -1 ? '∞' : planInfo.limit }} árbitros disponibles.
                    </span>
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
                        :disabled="!planInfo?.canCreate"
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
                        :disabled="!planInfo?.canCreate"
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
                            :disabled="!planInfo?.canCreate"
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
                            :disabled="!planInfo?.canCreate"
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
                        :disabled="!planInfo?.canCreate"
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
                            :disabled="!planInfo?.canCreate"
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
                    Árbitro creado correctamente
                </div>
                <div v-else />

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing || !planInfo?.canCreate"
                    class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                >
                    {{ !planInfo?.canCreate ? 'Límite alcanzado' : 'Crear Árbitro' }}
                </PrimaryButton>
            </div>

        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>