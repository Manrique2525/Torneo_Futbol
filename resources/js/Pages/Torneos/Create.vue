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
    fair_play_automatico: props.torneo?.fair_play_automatico ?? false,
    ida_y_vuelta: props.torneo?.ida_y_vuelta ?? false,
    formato_relampago: props.torneo?.formato_relampago ?? null,
    tiene_playoff: props.torneo?.tiene_playoff ?? false,
    playoff_equipos: props.torneo?.playoff_equipos ?? null,
    playoff_ida_vuelta: props.torneo?.playoff_ida_vuelta ?? false,
    hora_inicio: props.torneo?.hora_inicio ?? '12:00',
    duracion_minutos: props.torneo?.duracion_minutos ?? 90,
    precio_inscripcion: props.torneo?.precio_inscripcion ?? null,
    pago_requerido: props.torneo?.pago_requerido ?? false,
});

const formatoRelampagoOptions = [
    { label: 'Fase de Grupos', value: 'grupos' },
    { label: 'Eliminación Directa', value: 'eliminacion_directa' },
];
const selectedFormatoRelampago = ref(formatoRelampagoOptions.find(o => o.value === props.torneo?.formato_relampago) ?? null);
const onFormatoRelampagoChange = (o) => form.formato_relampago = o?.value ?? null;

const playoffEquiposOptions = [
    { label: '2 equipos', value: 2 },
    { label: '4 equipos', value: 4 },
    { label: '8 equipos', value: 8 },
    { label: '16 equipos', value: 16 },
];
const selectedPlayoffEquipos = ref(playoffEquiposOptions.find(o => o.value === props.torneo?.playoff_equipos) ?? null);
const onPlayoffEquiposChange = (o) => form.playoff_equipos = o?.value ?? null;

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
        <div class="flex items-center gap-4 px-4 md:px-8 py-4 md:py-5 border-b border-slate-100 dark:border-slate-800">
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
        <form @submit.prevent="submit" class="px-4 md:px-8 py-5 md:py-7">

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

                <!-- Fair Play Automatico -->
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer select-none group">
                        <div class="relative">
                            <input
                                type="checkbox"
                                v-model="form.fair_play_automatico"
                                class="peer sr-only"
                            >
                            <div class="h-6 w-11 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary transition-all"></div>
                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">
                                Fair Play Automatico
                            </span>
                            <p class="text-[11px] text-slate-400 font-medium">
                                Descuenta puntos de fair play por tarjetas y faltas automáticamente
                            </p>
                        </div>
                    </label>
                    <InputError :message="form.errors.fair_play_automatico" class="mt-1.5 ml-14" />
                </div>

            </div>

            <!-- SECCIÓN FORMATO DE TORNEO -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-5 mb-7">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary !text-lg">tune</span>
                    Formato del Torneo
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Ida y Vuelta (Liga y Copa) -->
                    <div v-if="form.tipo === 'liga' || form.tipo === 'copa'" class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <div class="relative">
                                <input type="checkbox" v-model="form.ida_y_vuelta" class="peer sr-only">
                                <div class="h-6 w-11 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary transition-all"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">
                                    Ida y Vuelta
                                </span>
                                <p class="text-[11px] text-slate-400 font-medium">
                                    Cada equipo juega 2 veces contra cada rival (local y visitante)
                                </p>
                            </div>
                        </label>
                        <InputError :message="form.errors.ida_y_vuelta" class="mt-1.5 ml-14" />
                    </div>

                    <!-- Formato Relámpago -->
                    <div v-if="form.tipo === 'relampago'" class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                            Formato
                        </label>
                        <VSelectCustom
                            v-model="selectedFormatoRelampago"
                            :options="formatoRelampagoOptions"
                            label="label"
                            :clearable="false"
                            placeholder="Seleccionar formato..."
                            @update:modelValue="onFormatoRelampagoChange"
                        />
                        <InputError :message="form.errors.formato_relampago" class="mt-1.5" />
                        <p v-if="form.formato_relampago === 'eliminacion_directa'" class="text-[11px] text-amber-500 font-medium mt-2 ml-1">
                            <span class="material-symbols-outlined !text-sm align-middle mr-1">info</span>
                            Eliminación directa: sin ida y vuelta, un solo partido por llave
                        </p>
                    </div>

                    <!-- Tiene Playoff -->
                    <div v-if="form.tipo === 'liga' || form.tipo === 'copa' || (form.tipo === 'relampago' && form.formato_relampago === 'grupos')" class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <div class="relative">
                                <input type="checkbox" v-model="form.tiene_playoff" class="peer sr-only">
                                <div class="h-6 w-11 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary transition-all"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">
                                    Tiene Playoff
                                </span>
                                <p class="text-[11px] text-slate-400 font-medium">
                                    Los mejores equipos clasifican a una fase eliminatoria
                                </p>
                            </div>
                        </label>
                        <InputError :message="form.errors.tiene_playoff" class="mt-1.5 ml-14" />
                    </div>

                    <!-- Playoff Equipos -->
                    <div v-if="form.tiene_playoff && (form.tipo === 'liga' || form.tipo === 'copa' || (form.tipo === 'relampago' && form.formato_relampago === 'grupos'))">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                            Equipos en Playoff
                        </label>
                        <VSelectCustom
                            v-model="selectedPlayoffEquipos"
                            :options="playoffEquiposOptions"
                            label="label"
                            :clearable="false"
                            placeholder="Seleccionar..."
                            @update:modelValue="onPlayoffEquiposChange"
                        />
                        <InputError :message="form.errors.playoff_equipos" class="mt-1.5" />
                    </div>

                    <!-- Playoff Ida y Vuelta -->
                    <div v-if="form.tiene_playoff && (form.tipo === 'liga' || form.tipo === 'copa' || (form.tipo === 'relampago' && form.formato_relampago === 'grupos'))" class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <div class="relative">
                                <input type="checkbox" v-model="form.playoff_ida_vuelta" class="peer sr-only">
                                <div class="h-6 w-11 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary transition-all"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">
                                    Playoff Ida y Vuelta
                                </span>
                                <p class="text-[11px] text-slate-400 font-medium">
                                    Cada llave del playoff se juega a 2 partidos (ida y vuelta)
                                </p>
                            </div>
                        </label>
                        <InputError :message="form.errors.playoff_ida_vuelta" class="mt-1.5 ml-14" />
                    </div>

                </div>
            </div>

            <!-- SECCIÓN PAGO DE INSCRIPCIÓN -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-5 mb-7">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary !text-lg">payments</span>
                    Pago de Inscripción
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Pago Requerido -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <div class="relative">
                                <input type="checkbox" v-model="form.pago_requerido" class="peer sr-only">
                                <div class="h-6 w-11 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-primary transition-all"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">
                                    Requerir pago de inscripción
                                </span>
                                <p class="text-xs text-slate-400">Los equipos deberán pagar para confirmar su inscripción</p>
                            </div>
                        </label>
                        <InputError :message="form.errors.pago_requerido" class="mt-1.5 ml-14" />
                    </div>

                    <!-- Precio -->
                    <div v-if="form.pago_requerido">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                            Precio de Inscripción
                        </label>
                        <div class="flex gap-2 items-start">
                            <div class="flex-1">
                                <input
                                    type="number"
                                    v-model="form.precio_inscripcion"
                                    min="0"
                                    max="999999.99"
                                    step="0.01"
                                    class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                           focus:border-primary focus:ring-2 focus:ring-primary/20
                                           rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                           transition-all outline-none"
                                    placeholder="0.00"
                                >
                            </div>
                            <div class="shrink-0 mt-1">
                                <span class="inline-flex items-center px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-sm font-bold text-slate-500">MXN</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.precio_inscripcion" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <!-- SECCIÓN CONFIGURACIÓN DE PARTIDOS -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-5 mb-7">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary !text-lg">schedule</span>
                    Configuración de Partidos
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Hora de Inicio -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                            Hora de Inicio por Defecto
                        </label>
                        <input
                            type="time"
                            v-model="form.hora_inicio"
                            class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                        <p class="text-[11px] text-slate-400 font-medium mt-1 ml-1">
                            Hora base para los partidos al generar el calendario
                        </p>
                        <InputError :message="form.errors.hora_inicio" class="mt-1.5" />
                    </div>

                    <!-- Duración -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                            Duración por Defecto (minutos)
                        </label>
                        <input
                            type="number"
                            v-model="form.duracion_minutos"
                            min="15"
                            max="300"
                            class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                   focus:border-primary focus:ring-2 focus:ring-primary/20
                                   rounded-xl py-3 px-4 text-sm text-slate-800 dark:text-white
                                   transition-all outline-none"
                        >
                        <p class="text-[11px] text-slate-400 font-medium mt-1 ml-1">
                            Duración de cada partido (15 - 300 minutos)
                        </p>
                        <InputError :message="form.errors.duracion_minutos" class="mt-1.5" />
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

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
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div v-if="form.recentlySuccessful"
                    class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    {{ isEditing ? 'Torneo actualizado correctamente' : 'Torneo creado correctamente' }}
                </div>
                <div v-else class="hidden sm:block" />

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