<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableEstadisticas from '@/Components/TableEstadisticas.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import jsPDF from 'jspdf';
import { autoTable } from 'jspdf-autotable';

const props = defineProps({
    torneo: Object,
    estadisticas: Object,
    flash: Object,
});

const tabActivo = ref('goleo');

const tabs = [
    { id: 'goleo', label: 'Goleo', icon: 'sports_soccer' },
    { id: 'asistencias', label: 'Asistencias', icon: 'assist_walker' },
    { id: 'tarjetas_amarillas', label: 'T. Amarillas', icon: 'style' },
    { id: 'tarjetas_rojas', label: 'T. Rojas', icon: 'style' },
    { id: 'faltas', label: 'Faltas', icon: 'gavel' },
];

const tabConfig = computed(() => {
    const configs = {
        goleo: { titulo: 'Tabla de Goleo', icono: 'sports_soccer', color: 'emerald', valorCampo: 'goles', valorLabel: 'Goles' },
        asistencias: { titulo: 'Asistencias', icono: 'assist_walker', color: 'primary', valorCampo: 'asistencias', valorLabel: 'Asist.' },
        tarjetas_amarillas: { titulo: 'Tarjetas Amarillas', icono: 'style', color: 'amber', valorCampo: 'total', valorLabel: 'TA' },
        tarjetas_rojas: { titulo: 'Tarjetas Rojas', icono: 'style', color: 'red', valorCampo: 'total', valorLabel: 'TR' },
        faltas: { titulo: 'Faltas', icono: 'gavel', color: 'slate', valorCampo: 'total', valorLabel: 'Faltas' },
    };
    return configs[tabActivo.value];
});

const tipoLabel = computed(() => {
    const labels = { liga: 'Liga', copa: 'Copa', relampago: 'Relámpago' };
    return labels[props.torneo?.tipo] ?? props.torneo?.tipo;
});

const descargarPDF = () => {
    const doc = new jsPDF('portrait', 'mm', 'letter');
    const primary = '#10b77f';
    const pageWidth = doc.internal.pageSize.getWidth();

    doc.setFillColor(16, 183, 127);
    doc.rect(0, 0, pageWidth, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text(props.torneo?.nombre ?? 'Torneo', pageWidth / 2, 14, { align: 'center' });
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Estadísticas — ' + tabConfig.value.titulo, pageWidth / 2, 23, { align: 'center' });

    doc.setFontSize(8);
    doc.setTextColor(120, 120, 120);
    const hoy = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    doc.text('Generado el ' + hoy, pageWidth - 15, 40, { align: 'right' });

    const isGoleo = tabActivo.value === 'goleo';
    const isAsistencias = tabActivo.value === 'asistencias';
    const valorKey = isGoleo ? 'goles' : isAsistencias ? 'asistencias' : 'total';

    const columns = [
        { header: '#', dataKey: 'pos' },
        { header: 'Jugador', dataKey: 'jugador' },
        { header: 'Equipo', dataKey: 'equipo' },
        { header: tabConfig.value.valorLabel, dataKey: 'valor' },
    ];

    const rows = (props.estadisticas[tabActivo.value] ?? []).map((row, idx) => ({
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
    doc.text(tabConfig.value.titulo + ' — ' + tipoLabel.value, pageWidth / 2, finalY + 3, { align: 'center' });

    const fileName = `estadisticas-${tabActivo.value}-${props.torneo?.nombre?.toLowerCase().replace(/\s+/g, '-') || 'torneo'}.pdf`;
    doc.save(fileName);
};
</script>

<template>
<Head :title="`Estadísticas — ${torneo.nombre}`" />

<AuthenticatedLayout>
<div class="w-full space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <Link
                :href="route('standings.index', torneo.id)"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary mb-2"
            >
                <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
                Posiciones
            </Link>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Estadísticas del <span class="text-primary">Torneo</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ torneo?.nombre }} — {{ tipoLabel }}
            </p>
        </div>
        <button
            @click="descargarPDF"
            class="px-4 py-2.5 rounded-xl bg-white dark:bg-[#1A2C26] border-2 border-primary text-primary font-black uppercase text-[10px] tracking-widest hover:bg-primary hover:text-white transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-1">picture_as_pdf</span>
            PDF — {{ tabConfig.titulo }}
        </button>
    </div>

    <!-- Flash -->
    <div v-if="flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2">
        <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="tabActivo = tab.id"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="tabActivo === tab.id
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            <span class="material-symbols-outlined !text-sm">{{ tab.icon }}</span>
            {{ tab.label }}
        </button>
    </div>

    <!-- Tabla activa -->
    <TableEstadisticas
        :key="tabActivo"
        :titulo="tabConfig.titulo"
        :icono="tabConfig.icono"
        :color="tabConfig.color"
        :datos="estadisticas[tabActivo] ?? []"
        :valor-campo="tabConfig.valorCampo"
        :valor-label="tabConfig.valorLabel"
    />

</div>
</AuthenticatedLayout>
</template>
