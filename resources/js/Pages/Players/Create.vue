<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    equipos: Array,
    planInfo: Object,
    constantes: Object
});

const posicionesJugador = props.constantes?.posiciones_jugador || {};
const estadosJugador = props.constantes?.estados_jugador || {};

// Opciones para selects
const equipoOptions = (props.equipos || []).map(e => ({
    label: e.name,
    value: e.id
}));

const posicionOptions = Object.entries(posicionesJugador).map(([key, label]) => ({
    label: label,
    value: key
}));

const estadoOptions = Object.entries(estadosJugador).map(([key, label]) => ({
    label: label,
    value: key
}));

const form = useForm({
    nombre: '',
    equipo_id: '',
    numero: '',
    posicion: '',
    edad: '',
    curp: '',
    foto: null,
    estado: 'activo',
});

const selectedEquipo = ref(null);
const selectedPosicion = ref(null);
const selectedEstado = ref(estadoOptions.find(o => o.value === 'activo'));

const onEquipoChange = (o) => form.equipo_id = o?.value ?? '';
const onPosicionChange = (o) => form.posicion = o?.value ?? '';
const onEstadoChange = (o) => form.estado = o?.value ?? 'activo';

// Preview de foto
const fotoPreview = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            fotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('players.store'));
};
</script>

<template>
<Head title="Nuevo Jugador" />

<AuthenticatedLayout>
<div class="w-full">

    <!-- 🔙 VOLVER -->
    <Link
        :href="route('players.index')"
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
                Límite de jugadores alcanzado
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-500">
                {{ planInfo?.isUnlimited 
                    ? 'Tu plan no permite crear más jugadores' 
                    : `Tu plan actual permite un máximo de ${planInfo.limit} jugadores. Contacta con soporte para aumentar el límite.`
                }}
            </p>
        </div>
    </div>

    <!-- 🧾 CARD -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">person_add</span>
            </div>
            <div>
                <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Nuevo Jugador
                </h2>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                    Completa la información para registrar un nuevo jugador.
                    <span v-if="planInfo?.remaining !== null && planInfo.remaining > 0" 
                          class="text-primary font-bold">
                        Te quedan {{ planInfo.remaining }} de {{ planInfo.limit === -1 ? '∞' : planInfo.limit }} jugadores disponibles.
                    </span>
                </p>
            </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="px-8 py-7" enctype="multipart/form-data">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                <!-- Foto -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Foto del Jugador
                    </label>
                    
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shadow-lg">
                                <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center overflow-hidden">
                                    <img 
                                        v-if="fotoPreview" 
                                        :src="fotoPreview" 
                                        alt="Foto preview" 
                                        class="h-full w-full object-cover rounded-[14px]"
                                    >
                                    <span v-else class="material-symbols-outlined text-4xl text-primary">person</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label class="cursor-pointer">
                                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-white/5 rounded-xl hover:bg-primary/10 transition-all">
                                    <span class="material-symbols-outlined text-primary text-xl">upload</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                        {{ form.foto ? form.foto.name : 'Seleccionar foto...' }}
                                    </span>
                                </div>
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    class="hidden" 
                                    @change="onFileChange"
                                >
                            </label>
                            <p class="text-[10px] text-slate-400 mt-2">Formatos: JPG, PNG. Máx 2MB</p>
                            <InputError :message="form.errors.foto" class="mt-1.5" />
                        </div>
                    </div>
                </div>

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
                        placeholder="Ej. Lionel Messi"
                        :disabled="!planInfo?.canCreate"
                    >
                    <InputError :message="form.errors.nombre" class="mt-1.5" />
                </div>

                <!-- Equipo -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Equipo *
                    </label>
                    <VSelectCustom
                        v-model="selectedEquipo"
                        :options="equipoOptions"
                        label="label"
                        :clearable="false"
                        placeholder="Seleccionar equipo..."
                        @update:modelValue="onEquipoChange"
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.equipo_id" class="mt-1.5" />
                </div>

                <!-- Número -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Número de Camiseta
                    </label>
                    <input
                        v-model="form.numero"
                        type="number"
                        min="1"
                        max="99"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="10"
                        :disabled="!planInfo?.canCreate"
                    >
                    <InputError :message="form.errors.numero" class="mt-1.5" />
                </div>

                <!-- Posición -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Posición
                    </label>
                    <VSelectCustom
                        v-model="selectedPosicion"
                        :options="posicionOptions"
                        label="label"
                        :clearable="true"
                        placeholder="Seleccionar posición..."
                        @update:modelValue="onPosicionChange"
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.posicion" class="mt-1.5" />
                </div>

                <!-- Edad -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Edad
                    </label>
                    <input
                        v-model="form.edad"
                        type="number"
                        min="15"
                        max="60"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none"
                        placeholder="25"
                        :disabled="!planInfo?.canCreate"
                    >
                    <InputError :message="form.errors.edad" class="mt-1.5" />
                </div>

                <!-- CURP -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        CURP
                    </label>
                    <input
                        v-model="form.curp"
                        type="text"
                        maxlength="18"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                               focus:border-primary focus:ring-2 focus:ring-primary/20
                               rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                               placeholder:text-slate-400 transition-all outline-none uppercase"
                        placeholder="XXXX000000XXXXXXX"
                        :disabled="!planInfo?.canCreate"
                    >
                    <InputError :message="form.errors.curp" class="mt-1.5" />
                </div>

                <!-- Estado -->
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
                        :disabled="!planInfo?.canCreate"
                    />
                    <InputError :message="form.errors.estado" class="mt-1.5" />
                </div>

            </div>

            <!-- FOOTER -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    Jugador creado correctamente
                </div>
                <div v-else />

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing || !planInfo?.canCreate"
                    class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                >
                    {{ !planInfo?.canCreate ? 'Límite alcanzado' : 'Crear Jugador' }}
                </PrimaryButton>
            </div>

        </form>
    </div>
</div>
</AuthenticatedLayout>
</template>