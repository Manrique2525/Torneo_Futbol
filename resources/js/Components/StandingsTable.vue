<script setup>
import { ref, computed } from 'vue';
import TableEstadisticas from '@/Components/TableEstadisticas.vue';
import jsPDF from 'jspdf';
import { autoTable } from 'jspdf-autotable';

const props = defineProps({
    standings: { type: Array, required: true },
    vista: { type: String, default: 'posiciones' },
    puedeRecalcular: { type: Boolean, default: false },
    torneoId: { type: [Number, String], default: null },
});

const emit = defineEmits(['team-click']);

const equipoModal = ref(null);
const modalAbierto = ref(false);
const cargando = ref(false);
const statsEquipo = ref(null);
const errorModal = ref(null);

const getPosicionClass = (posicion, total) => {
    if (props.vista !== 'posiciones') {
        return 'text-slate-600 bg-slate-500/10';
    }
    if (posicion === 1) return 'text-amber-600 bg-amber-500/10';
    if (posicion <= 4 && total >= 6) return 'text-emerald-600 bg-emerald-500/10';
    if (posicion > total - 2 && total >= 6) return 'text-red-600 bg-red-500/10';
    return 'text-slate-600 bg-slate-500/10';
};

const getDgClass = (dg) => {
    if (dg > 0) return 'text-emerald-600';
    if (dg < 0) return 'text-red-600';
    return 'text-slate-400';
};

const getShieldStyle = (shield) => ({
    backgroundImage: shield ? `url(/storage/${shield})` : 'none',
});

const posicionKey = (s) => {
    return props.vista === 'posiciones'
        ? s.posicion_posiciones
        : s.posicion_rendimiento;
};

const abrirModalEquipo = async (s) => {
    if (!s.torneo_equipo_id) return;
    equipoModal.value = s;
    modalAbierto.value = true;
    cargando.value = true;
    modalTab.value = 'goleo';
    errorModal.value = null;
    statsEquipo.value = null;
    try {
        const url = route('estadisticas.equipo', [props.torneoId, s.torneo_equipo_id]);
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) {
            const text = await res.text().catch(() => '');
            throw new Error(`Error ${res.status}${text ? ': ' + text.slice(0, 200) : ''}`);
        }
        statsEquipo.value = await res.json();
    } catch (e) {
        errorModal.value = e.message;
    } finally {
        cargando.value = false;
    }
};

const modalTab = ref('goleo');

const modalTabs = [
    { id: 'goleo', label: 'Goleo', icon: 'sports_soccer' },
    { id: 'asistencias', label: 'Asistencias', icon: 'assist_walker' },
    { id: 'tarjetas_amarillas', label: 'T. Amarillas', icon: 'style' },
    { id: 'tarjetas_rojas', label: 'T. Rojas', icon: 'style' },
    { id: 'faltas', label: 'Faltas', icon: 'gavel' },
];

const modalTabConfig = computed(() => {
    const configs = {
        goleo: { titulo: 'Goleo', icono: 'sports_soccer', color: 'emerald', valorCampo: 'goles', valorLabel: 'Goles' },
        asistencias: { titulo: 'Asistencias', icono: 'assist_walker', color: 'primary', valorCampo: 'asistencias', valorLabel: 'Asist.' },
        tarjetas_amarillas: { titulo: 'Tarjetas Amarillas', icono: 'style', color: 'amber', valorCampo: 'total', valorLabel: 'TA' },
        tarjetas_rojas: { titulo: 'Tarjetas Rojas', icono: 'style', color: 'red', valorCampo: 'total', valorLabel: 'TR' },
        faltas: { titulo: 'Faltas', icono: 'gavel', color: 'slate', valorCampo: 'total', valorLabel: 'Faltas' },
    };
    return configs[modalTab.value];
});

const cerrarModal = () => {
    modalAbierto.value = false;
    equipoModal.value = null;
    statsEquipo.value = null;
    errorModal.value = null;
};

const descargarPDFModal = () => {
    if (!statsEquipo.value) return;
    const doc = new jsPDF('portrait', 'mm', 'letter');
    const pageWidth = doc.internal.pageSize.getWidth();

    doc.setFillColor(16, 183, 127);
    doc.rect(0, 0, pageWidth, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text(equipoModal.value?.equipo?.nombre ?? 'Equipo', pageWidth / 2, 14, { align: 'center' });
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Estadísticas — ' + modalTabConfig.value.titulo, pageWidth / 2, 23, { align: 'center' });

    doc.setFontSize(8);
    doc.setTextColor(120, 120, 120);
    const hoy = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    doc.text('Generado el ' + hoy, pageWidth - 15, 40, { align: 'right' });

    const isGoleo = modalTab.value === 'goleo';
    const isAsistencias = modalTab.value === 'asistencias';
    const valorKey = isGoleo ? 'goles' : isAsistencias ? 'asistencias' : 'total';

    const columns = [
        { header: '#', dataKey: 'pos' },
        { header: 'Jugador', dataKey: 'jugador' },
        { header: 'Equipo', dataKey: 'equipo' },
        { header: modalTabConfig.value.valorLabel, dataKey: 'valor' },
    ];

    const rows = (statsEquipo.value[modalTab.value] ?? []).map((row, idx) => ({
        pos: idx + 1,
        jugador: row.jugador_nombre,
        equipo: row.equipo_nombre,
        valor: row[valorKey],
    }));

    autoTable(doc, {
        columns,
        body: rows,
        startY: 45,
        theme: 'grid',
        headStyles: { fillColor: [16, 183, 127], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 9, halign: 'center', cellPadding: 3 },
        bodyStyles: { fontSize: 9, cellPadding: 3 },
        columnStyles: {
            pos: { halign: 'center', cellWidth: 12 },
            jugador: { halign: 'left', cellWidth: 'auto' },
            equipo: { halign: 'left', cellWidth: 'auto' },
            valor: { halign: 'center', cellWidth: 20 },
        },
        alternateRowStyles: { fillColor: [245, 250, 248] },
    });

    const finalY = doc.lastAutoTable.finalY + 8;
    doc.setFontSize(7);
    doc.setTextColor(150, 150, 150);
    doc.text(modalTabConfig.value.titulo + ' — ' + (equipoModal.value?.equipo?.nombre ?? ''), pageWidth / 2, finalY + 3, { align: 'center' });

    const fileName = `estadisticas-${modalTab.value}-${equipoModal.value?.equipo?.nombre?.toLowerCase().replace(/\s+/g, '-') || 'equipo'}.pdf`;
    doc.save(fileName);
};
</script>

<template>
    <div class="overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">#</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Equipo</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PJ</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PG</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PE</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">PP</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">GF</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">GC</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">DG</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">FP</th>
                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Pts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                <tr
                    v-for="s in standings"
                    :key="s.id"
                    @click="abrirModalEquipo(s)"
                    class="cursor-pointer group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                >
                    <td class="p-4 text-center">
                        <span
                            class="inline-flex items-center justify-center h-7 w-7 rounded-lg text-[10px] font-black"
                            :class="getPosicionClass(posicionKey(s), standings.length)"
                        >
                            {{ posicionKey(s) }}
                        </span>
                    </td>

                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-white/5 bg-cover bg-center border border-slate-200 dark:border-slate-700 shrink-0"
                                :style="getShieldStyle(s.equipo?.shield)"
                            >
                                <div v-if="!s.equipo?.shield" class="h-full w-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm text-slate-300 dark:text-slate-600">shield</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                    {{ s.equipo?.nombre ?? '—' }}
                                </span>
                                <span class="material-symbols-outlined !text-xs text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-all">bar_chart</span>
                            </div>
                        </div>
                    </td>

                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.pj }}</td>
                    <td class="p-4 text-center text-sm font-medium text-emerald-600">{{ s.pg }}</td>
                    <td class="p-4 text-center text-sm font-medium text-amber-600">{{ s.pe }}</td>
                    <td class="p-4 text-center text-sm font-medium text-red-600">{{ s.pp }}</td>
                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.gf }}</td>
                    <td class="p-4 text-center text-sm font-medium text-slate-700 dark:text-slate-300">{{ s.gc }}</td>
                    <td class="p-4 text-center text-sm font-black" :class="getDgClass(s.dg)">
                        {{ s.dg > 0 ? '+' : '' }}{{ s.dg }}
                    </td>
                    <td class="p-4 text-center text-sm font-medium text-primary">{{ s.fair_play }}</td>
                    <td class="p-4 text-center">
                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ s.pts }}</span>
                    </td>
                </tr>

                <tr v-if="!standings?.length">
                    <td colspan="11" class="text-center py-10 text-slate-400">
                        No hay datos de posiciones disponibles
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal estadísticas por equipo -->
    <Teleport to="body">
        <div
            v-if="modalAbierto"
            class="fixed inset-0 z-50 flex items-start justify-center pt-10 md:pt-20"
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="cerrarModal"></div>
            <div class="relative bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl w-full max-w-4xl max-h-[80vh] overflow-y-auto mx-4 z-10">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-[#1A2C26] z-10 p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30"
                        >
                            <span class="material-symbols-outlined text-xl">bar_chart</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                Estadísticas — {{ equipoModal?.equipo?.nombre ?? '' }}
                            </h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                Resumen por equipo
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="statsEquipo"
                            @click="descargarPDFModal"
                            class="px-3 py-2 rounded-xl bg-white dark:bg-[#1A2C26] border-2 border-primary text-primary font-black uppercase text-[10px] tracking-widest hover:bg-primary hover:text-white transition-all"
                        >
                            <span class="material-symbols-outlined !text-sm mr-1">picture_as_pdf</span>
                            PDF
                        </button>
                        <button
                            @click="cerrarModal"
                            class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-white/10 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-white/20 transition-colors"
                        >
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div v-if="cargando" class="flex items-center justify-center py-10">
                        <span class="material-symbols-outlined animate-spin text-primary text-3xl">refresh</span>
                        <span class="ml-3 text-sm font-bold text-slate-500">Cargando estadísticas...</span>
                    </div>
                    <div v-else-if="errorModal" class="bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl text-sm font-bold">
                        {{ errorModal }}
                    </div>
                    <template v-else-if="statsEquipo">
                        <!-- Tabs -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button
                                v-for="tab in modalTabs"
                                :key="tab.id"
                                @click="modalTab = tab.id"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="modalTab === tab.id
                                    ? 'bg-primary text-white shadow-lg shadow-primary/20'
                                    : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
                            >
                                <span class="material-symbols-outlined !text-sm">{{ tab.icon }}</span>
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- Tabla activa -->
                        <TableEstadisticas
                            :key="modalTab + '-' + equipoModal.id"
                            :titulo="modalTabConfig.titulo"
                            :icono="modalTabConfig.icono"
                            :color="modalTabConfig.color"
                            :datos="statsEquipo[modalTab] ?? []"
                            :valor-campo="modalTabConfig.valorCampo"
                            :valor-label="modalTabConfig.valorLabel"
                        />
                    </template>
                </div>
            </div>
        </div>
    </Teleport>
</template>
