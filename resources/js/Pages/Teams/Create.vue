<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    delegados: Array,
    planInfo: Object,
    constantes: Object
});

const coloresEquipos = props.constantes?.colores_equipos || {};

// Convertir colores a opciones para VSelect
const colorOptions = Object.entries(coloresEquipos).map(([nombre, hex]) => ({
    label: nombre,
    value: nombre,
    hex: hex
}));

const form = useForm({
    name: '',
    colors: '',
    delegado_id: '',
    phone: '',
    email: '',
    shield: null,
    status: 'active',
});

// Para VSelectCustom - delegados
const delegadosOptions = (props.delegados || []).map(d => ({ 
    label: d.name, 
    value: d.id 
}));

const selectedDelegado = ref(null);
const selectedColors = ref([]); // Array de colores seleccionados

// Estado options
const estadoOptions = [
    { label: 'Activo', value: 'active' },
    { label: 'Suspendido', value: 'suspended' },
    { label: 'Inactivo', value: 'inactive' },
];

const selectedEstado = ref(estadoOptions[0]);

// Handlers
const onDelegadoChange = (o) => form.delegado_id = o?.value ?? '';
const onEstadoChange = (o) => form.status = o?.value ?? 'active';

// Manejar cambio de colores
const onColorsChange = (selected) => {
    selectedColors.value = selected || [];
    form.colors = selectedColors.value.map(c => c.value).join(', ');
};

// Preview de imagen
const shieldPreview = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.shield = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            shieldPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('teams.store'));
};
</script>

<template>
<Head title="Nuevo Equipo" />

<AuthenticatedLayout>
<div class="w-full">

    <!-- 🔙 VOLVER -->
    <Link
        :href="route('teams.index')"
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
                Límite de equipos alcanzado
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-500">
                {{ planInfo?.isUnlimited 
                    ? 'Tu plan no permite crear más equipos' 
                    : `Tu plan actual permite un máximo de ${planInfo.limit} equipos. Contacta con soporte para aumentar el límite.`
                }}
            </p>
        </div>
    </div>

    <!-- 🧾 CARD -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">shield</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Nuevo Equipo
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Completa la información para registrar un nuevo equipo.
                    <span v-if="planInfo?.remaining !== null && planInfo.remaining > 0" 
                          class="text-primary font-bold">
                        Te quedan {{ planInfo.remaining }} de {{ planInfo.limit === -1 ? '∞' : planInfo.limit }} equipos disponibles.
                    </span>
                </p>
            </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="px-8 py-7" enctype="multipart/form-data">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                <!-- Escudo / Logo -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Escudo del Equipo
                    </label>
                    
                    <div class="flex items-center gap-6">
                        <!-- Preview -->
                        <div class="relative">
                            <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shadow-lg">
                                <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center overflow-hidden">
                                    <img 
                                        v-if="shieldPreview" 
                                        :src="shieldPreview" 
                                        alt="Escudo preview" 
                                        class="h-full w-full object-cover rounded-[14px]"
                                    >
                                    <span v-else class="material-symbols-outlined text-4xl text-primary">shield</span>
                                </div>
                            </div>
                        </div>

                        <!-- Input file -->
                        <div class="flex-1">
                            <label class="cursor-pointer">
                                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-white/5 rounded-xl hover:bg-primary/10 transition-all">
                                    <span class="material-symbols-outlined text-primary text-xl">upload</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                        {{ form.shield ? form.shield.name : 'Seleccionar escudo...' }}
                                    </span>
                                </div>
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    class="hidden" 
                                    @change="onFileChange"
                                >
                            </label>
                            <p class="text-[10px] text-slate-400 mt-2">Formatos: JPG, PNG, SVG. Máx 2MB</p>
                            <InputError :message="form.errors.shield" class="mt-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Nombre -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Nombre del Equipo *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="Ej. Real Madrid FC"
                        :disabled="!planInfo?.canCreate"
                    >
                    <InputError :message="form.errors.name" class="mt-1.5" />
                </div>

                <!-- Colores (Selector múltiple) -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Colores del Equipo
                    </label>
                    <VSelectCustom
                        v-model="selectedColors"
                        :options="colorOptions"
                        label="label"
                        :reduce="opt => opt"
                        :clearable="true"
                        :multiple="true"
                        placeholder="Seleccionar colores..."
                        @update:modelValue="onColorsChange"
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.colors" class="mt-1.5" />
                    
                    <!-- Preview de colores seleccionados -->
                    <div v-if="selectedColors.length > 0" class="flex gap-2 mt-3">
                        <div 
                            v-for="color in selectedColors" 
                            :key="color.value"
                            class="h-6 w-6 rounded-full border border-slate-200 shadow-sm"
                            :style="{ backgroundColor: color.hex }"
                            :title="color.label"
                        ></div>
                    </div>
                </div>

                <!-- Delegado (VSelect) -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Delegado / Responsable
                    </label>
                    <VSelectCustom
                        v-model="selectedDelegado"
                        :options="delegadosOptions"
                        label="label"
                        :clearable="true"
                        placeholder="Seleccionar delegado..."
                        @update:modelValue="onDelegadoChange"
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.delegado_id" class="mt-1.5" />
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
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.status" class="mt-1.5" />
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
                            v-model="form.phone"
                            type="text"
                            class="w-full pl-10 bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   placeholder:text-slate-400 transition-all outline-none"
                            placeholder="+54 11 1234-5678"
                            :disabled="!planInfo?.canCreate"
                        >
                    </div>
                    <InputError :message="form.errors.phone" class="mt-1.5" />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Email de contacto
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
                            placeholder="equipo@club.com"
                            :disabled="!planInfo?.canCreate"
                        >
                    </div>
                    <InputError :message="form.errors.email" class="mt-1.5" />
                </div>

            </div>

            <!-- FOOTER -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Equipo creado correctamente
                </div>
                <div v-else />

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing || !planInfo?.canCreate"
                    class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                >
                    {{ !planInfo?.canCreate ? 'Límite alcanzado' : 'Crear Equipo' }}
                </PrimaryButton>
            </div>

        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>