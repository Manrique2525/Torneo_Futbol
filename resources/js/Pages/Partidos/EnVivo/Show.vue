<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

import MarcadorEnVivo from '@/Components/EnVivo/MarcadorEnVivo.vue';
import TimelineEventos from '@/Components/EnVivo/TimelineEventos.vue';
import PanelJugadores from '@/Components/EnVivo/PanelJugadores.vue';
import ModalEvento from '@/Components/EnVivo/ModalEvento.vue';
import PanelAsistencia from '@/Components/EnVivo/PanelAsistencia.vue';
import AlertaPenal from '@/Components/EnVivo/AlertaPenal.vue';

const props = defineProps({
    partido: Object,
    eventos: Array,
    asistencias: Object,
    faltas_local: Number,
    faltas_visitante: Number,
    alerta_penal_local: Boolean,
    alerta_penal_visitante: Boolean,
    expulsados_local: Array,
    expulsados_visitante: Array,
    puede_registrar_eventos: Boolean,
    puede_cambiar_estado: Boolean,
    puede_registrar_asistencia_local: Boolean,
    puede_registrar_asistencia_visitante: Boolean,
    tipos_evento: Array,
    flash: Object,
});

const showModal = ref(false);
const modalData = ref({ jugador: null, tipo: '', equipoId: null });

const abrirModal = (data) => {
    modalData.value = {
        jugador: data.jugador,
        tipo: data.tipo,
        equipoId: data.equipo_id,
    };
    showModal.value = true;
};

const eliminarEvento = (evt) => {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: '¿Eliminar evento?',
        text: `Se eliminará el evento del minuto ${evt.minuto}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: isDark ? '#334155' : '#94a3b8',
        background: isDark ? '#1A2C26' : '#fff',
        color: isDark ? '#e2e8f0' : '#1e293b',
        iconColor: '#f59e0b',
        reverseButtons: true,
        buttonsStyling: true,
        customClass: {
            popup: 'rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-800 font-sans',
            title: 'text-base font-black uppercase tracking-tight pt-2',
            htmlContainer: 'text-sm text-slate-500 dark:text-slate-400',
            confirmButton: '!rounded-xl !py-2.5 !px-5 !text-xs !font-black !uppercase !tracking-wider !shadow-lg !shadow-red-500/20',
            cancelButton: '!rounded-xl !py-2.5 !px-5 !text-xs !font-black !uppercase !tracking-wider',
            icon: '!border-4 !rounded-2xl',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('partidos.en-vivo.eventos.destroy', evt.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};

const cambiarEstado = (accion) => {
    const ruta = `partidos.en-vivo.${accion}`;
    router.post(route(ruta, props.partido.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const guardarAsistencias = (lista) => {
    router.post(route('partidos.en-vivo.asistencias.store', props.partido.id), {
        asistencias: lista,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const mostrarAsistencia = computed(() => props.partido?.estado === 'descanso');
const enJuego = computed(() => props.partido?.estado === 'en_juego');
const finalizado = computed(() => props.partido?.estado === 'finalizado');
const esFut7 = computed(() => props.partido?.cancha?.tipo === 'futbol-7' || props.partido?.duracion_minutos <= 50);

const tabActivo = ref('marcador');

const tabs = computed(() => {
    const list = [
        { id: 'marcador', label: 'Marcador', icon: 'sports_score' },
        { id: 'local', label: 'Local', icon: 'shield' },
        { id: 'visitante', label: 'Visitante', icon: 'shield' },
    ];
    if (mostrarAsistencia.value) {
        list.push({ id: 'asistencia', label: 'Asistencia', icon: 'fact_check' });
    }
    return list;
});

const estadoLabel = computed(() => {
    const labels = {
        programado: 'Programado',
        en_juego: 'En Juego',
        descanso: 'Descanso',
        finalizado: 'Finalizado',
        suspendido: 'Suspendido',
        cancelado: 'Cancelado',
    };
    return labels[props.partido?.estado] ?? props.partido?.estado;
});

const estadoColor = computed(() => {
    const colors = {
        programado: 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        en_juego: 'bg-amber-500/10 text-amber-600 border-amber-500/20',
        descanso: 'bg-purple-500/10 text-purple-600 border-purple-500/20',
        finalizado: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
        suspendido: 'bg-red-500/10 text-red-600 border-red-500/20',
        cancelado: 'bg-slate-500/10 text-slate-600 border-slate-500/20',
    };
    return colors[props.partido?.estado] ?? 'bg-slate-100 text-slate-500';
});
</script>

<template>
<Head title="Partido en Vivo" />

<AuthenticatedLayout>
<div class="w-full space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <Link
                :href="route('partidos.index')"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary transition-all mb-2"
            >
                <span class="material-symbols-outlined mr-1 !text-lg">arrow_back</span>
                Partidos
            </Link>
            <h2 class="text-xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Partido <span class="text-primary">En Vivo</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ partido?.torneo?.nombre }} — Jornada {{ partido?.jornada?.nombre ?? '—' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border', estadoColor]">
                {{ estadoLabel }}
            </span>
        </div>
    </div>

    <!-- Flash -->
    <div v-if="flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>
    <div v-if="flash?.error" class="bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-red-600">error</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.error }}</span>
    </div>

    <!-- Alertas Penales -->
    <div class="space-y-2">
        <AlertaPenal
            :show="alerta_penal_local && esFut7"
            :equipo="partido?.equipo_local?.nombre"
        />
        <AlertaPenal
            :show="alerta_penal_visitante && esFut7"
            :equipo="partido?.equipo_visitante?.nombre"
        />
    </div>

    <!-- Controles de estado -->
    <div v-if="puede_cambiar_estado && !finalizado" class="flex flex-wrap gap-2">
        <button
            v-if="partido?.estado === 'programado'"
            @click="cambiarEstado('iniciar')"
            class="px-4 py-2.5 rounded-xl bg-primary text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-1">play_arrow</span>
            Iniciar Partido
        </button>

        <button
            v-if="partido?.estado === 'en_juego'"
            @click="cambiarEstado('descanso')"
            class="px-4 py-2.5 rounded-xl bg-purple-600 text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-purple-600/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-1">pause</span>
            Medio Tiempo
        </button>

        <button
            v-if="partido?.estado === 'descanso'"
            @click="cambiarEstado('segunda-mitad')"
            class="px-4 py-2.5 rounded-xl bg-primary text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-1">play_arrow</span>
            Iniciar 2ª Mitad
        </button>

        <button
            v-if="partido?.estado === 'en_juego'"
            @click="cambiarEstado('finalizar')"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-emerald-600/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-1">stop</span>
            Finalizar Partido
        </button>
    </div>

    <!-- Tabs solo en móvil -->
    <div class="lg:hidden flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
        <button
            v-for="t in tabs"
            :key="t.id"
            @click="tabActivo = t.id"
            class="px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-1.5 shrink-0"
            :class="tabActivo === t.id
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400'"
        >
            <span class="material-symbols-outlined !text-base">{{ t.icon }}</span>
            {{ t.label }}
        </button>
    </div>

    <!-- Layout principal -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Panel Local -->
        <div
            :class="[
                'lg:col-span-3 bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm',
                tabActivo === 'local' ? 'block' : 'hidden',
                'lg:block',
            ]"
        >
            <PanelJugadores
                :jugadores="partido?.equipo_local?.jugadores ?? []"
                :equipo-id="partido?.equipo_local?.equipo_id_real"
                :equipo-nombre="partido?.equipo_local?.nombre"
                :expulsados="expulsados_local"
                :puede-registrar="puede_registrar_eventos && enJuego"
                @evento="abrirModal"
            />
        </div>

        <!-- Centro: Marcador + Timeline -->
        <div
            :class="[
                'lg:col-span-6 space-y-6',
                tabActivo === 'marcador' || tabActivo === 'asistencia' ? 'block' : 'hidden',
                'lg:block',
            ]"
        >
            <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
                <MarcadorEnVivo
                    :partido="partido"
                    :expulsados-local="expulsados_local"
                    :expulsados-visitante="expulsados_visitante"
                />
            </div>

            <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
                <TimelineEventos
                    :eventos="eventos"
                    :puede-eliminar="puede_registrar_eventos"
                    :equipo-local-id="partido?.equipo_local?.id"
                    :equipo-visitante-id="partido?.equipo_visitante?.id"
                    @eliminar="eliminarEvento"
                />
            </div>
        </div>

        <!-- Panel Visitante -->
        <div
            :class="[
                'lg:col-span-3 bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm',
                tabActivo === 'visitante' ? 'block' : 'hidden',
                'lg:block',
            ]"
        >
            <PanelJugadores
                :jugadores="partido?.equipo_visitante?.jugadores ?? []"
                :equipo-id="partido?.equipo_visitante?.equipo_id_real"
                :equipo-nombre="partido?.equipo_visitante?.nombre"
                :expulsados="expulsados_visitante"
                :puede-registrar="puede_registrar_eventos && enJuego"
                @evento="abrirModal"
            />
        </div>
    </div>

    <!-- Pase de lista (solo en descanso) -->
    <div
        v-if="mostrarAsistencia"
        :class="[
            'grid grid-cols-1 md:grid-cols-2 gap-6',
            tabActivo === 'asistencia' || tabActivo === 'marcador' ? 'block' : 'hidden',
            'lg:grid',
        ]"
    >
        <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
            <PanelAsistencia
                :jugadores="partido?.equipo_local?.jugadores ?? []"
                :equipo-id="partido?.equipo_local?.id"
                :equipo-nombre="partido?.equipo_local?.nombre"
                :asistencias="asistencias"
                :mitad="partido?.mitad"
                :puede-registrar="puede_registrar_asistencia_local"
                @guardar="guardarAsistencias"
            />
        </div>
        <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
            <PanelAsistencia
                :jugadores="partido?.equipo_visitante?.jugadores ?? []"
                :equipo-id="partido?.equipo_visitante?.id"
                :equipo-nombre="partido?.equipo_visitante?.nombre"
                :asistencias="asistencias"
                :mitad="partido?.mitad"
                :puede-registrar="puede_registrar_asistencia_visitante"
                @guardar="guardarAsistencias"
            />
        </div>
    </div>

    <!-- Modal Evento -->
    <ModalEvento
        :show="showModal"
        :jugador-pre="modalData.jugador"
        :tipo-pre="modalData.tipo"
        :equipo-id="modalData.equipoId"
        :jugadores="modalData.equipoId === partido?.equipo_local?.equipo_id_real
            ? partido?.equipo_local?.jugadores
            : partido?.equipo_visitante?.jugadores"
        :tipos-evento="tipos_evento"
        :duracion="partido?.duracion_minutos ?? 90"
        @close="showModal = false"
        @guardado="showModal = false"
    />

</div>
</AuthenticatedLayout>
</template>
