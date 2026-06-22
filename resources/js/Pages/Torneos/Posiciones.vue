<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StandingsTable from '@/Components/StandingsTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCan } from '@/Shared/Composables/useCan';
import jsPDF from 'jspdf';
import { autoTable } from 'jspdf-autotable';

const props = defineProps({
    torneo: Object,
    es_liga: Boolean,
    grupos: Array,
    flash: Object,
});

const { can } = useCan();

const grupoActivo = ref(0);
const vista = ref(props.es_liga ? 'posiciones' : 'rendimiento');

const recalcular = () => {
    if (!confirm('¿Recalcular la tabla de posiciones?')) return;
    router.post(route('standings.recalcular', props.torneo.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const standingsOrdenados = computed(() => {
    const grupo = props.grupos?.[grupoActivo.value];
    if (!grupo) return [];
    return [...grupo.standings].sort((a, b) => {
        if (vista.value === 'posiciones') {
            return (a.posicion_posiciones ?? 999) - (b.posicion_posiciones ?? 999);
        }
        return (a.posicion_rendimiento ?? 999) - (b.posicion_rendimiento ?? 999);
    });
});

const leyenda = computed(() => {
    if (vista.value === 'posiciones') {
        return 'Ordenado por: Puntos → Diferencia → Goles a favor → Fair Play';
    }
    return 'Ordenado por: Partidos ganados → Diferencia → Goles a favor → Fair Play';
});

const tipoLabel = computed(() => {
    const labels = { liga: 'Liga', copa: 'Copa', relampago: 'Relámpago' };
    return labels[props.torneo?.tipo] ?? props.torneo?.tipo;
});

const descargarPDF = () => {
    const doc = new jsPDF('landscape', 'mm', 'letter');
    const primary = '#10b77f';
    const pageWidth = doc.internal.pageSize.getWidth();

    // ── Header ──
    doc.setFillColor(16, 183, 127);
    doc.rect(0, 0, pageWidth, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text(props.torneo?.nombre ?? 'Torneo', pageWidth / 2, 16, { align: 'center' });
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Tabla de Posiciones — ' + tipoLabel.value, pageWidth / 2, 25, { align: 'center' });

    // ── Fecha ──
    doc.setFontSize(8);
    doc.setTextColor(120, 120, 120);
    const hoy = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    doc.text('Generado el ' + hoy, pageWidth - 15, 40, { align: 'right' });

    // ── Columns (view = posiciones uses #, view = rendimiento uses #) ──
    const columns = [
        { header: '#', dataKey: 'pos' },
        { header: 'Equipo', dataKey: 'equipo' },
        { header: 'PJ', dataKey: 'pj' },
        { header: 'PG', dataKey: 'pg' },
        { header: 'PE', dataKey: 'pe' },
        { header: 'PP', dataKey: 'pp' },
        { header: 'GF', dataKey: 'gf' },
        { header: 'GC', dataKey: 'gc' },
        { header: 'DG', dataKey: 'dg' },
        { header: 'FP', dataKey: 'fp' },
        { header: 'Pts', dataKey: 'pts' },
    ];

    const groupName = props.grupos?.[grupoActivo.value]?.nombre ?? 'General';

    const rows = standingsOrdenados.value.map((s) => ({
        pos: vista.value === 'posiciones' ? s.posicion_posiciones : s.posicion_rendimiento,
        equipo: s.equipo?.nombre ?? '—',
        pj: s.pj,
        pg: s.pg,
        pe: s.pe,
        pp: s.pp,
        gf: s.gf,
        gc: s.gc,
        dg: (s.dg > 0 ? '+' : '') + s.dg,
        fp: s.fair_play,
        pts: s.pts,
    }));

    // ── Table ──
    autoTable(doc, {
        columns,
        body: rows,
        startY: 45,
        theme: 'grid',
        headStyles: {
            fillColor: [16, 183, 127],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            fontSize: 9,
            halign: 'center',
            cellPadding: 3,
        },
        bodyStyles: {
            fontSize: 9,
            cellPadding: 3,
        },
        columnStyles: {
            pos: { halign: 'center', cellWidth: 12 },
            equipo: { halign: 'left', cellWidth: 'auto' },
            pj: { halign: 'center', cellWidth: 15 },
            pg: { halign: 'center', cellWidth: 15 },
            pe: { halign: 'center', cellWidth: 15 },
            pp: { halign: 'center', cellWidth: 15 },
            gf: { halign: 'center', cellWidth: 15 },
            gc: { halign: 'center', cellWidth: 15 },
            dg: { halign: 'center', cellWidth: 15 },
            fp: { halign: 'center', cellWidth: 15 },
            pts: { halign: 'center', cellWidth: 15 },
        },
        alternateRowStyles: {
            fillColor: [245, 250, 248],
        },
        didDrawPage: (data) => {
            // Footer with group name
            doc.setFontSize(8);
            doc.setTextColor(120, 120, 120);
            doc.text(groupName + ' — Página ' + doc.internal.getNumberOfPages(), pageWidth / 2, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
        },
    });

    // ── Legend ──
    const finalY = doc.lastAutoTable.finalY + 8;
    doc.setFontSize(7);
    doc.setTextColor(150, 150, 150);
    doc.text('PJ: Partidos Jugados | PG: Partidos Ganados | PE: Partidos Empatados | PP: Partidos Perdidos', pageWidth / 2, finalY + 3, { align: 'center' });
    doc.text('GF: Goles a Favor | GC: Goles en Contra | DG: Diferencia de Goles | FP: Fair Play | Pts: Puntos', pageWidth / 2, finalY + 10, { align: 'center' });

    doc.save(`posiciones-${props.torneo?.nombre?.toLowerCase().replace(/\s+/g, '-') || 'torneo'}.pdf`);
};
</script>

<template>
<Head :title="`Posiciones — ${torneo.nombre}`" />

<AuthenticatedLayout>
<div class="w-full space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <Link
                :href="route('torneos.index')"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary mb-2"
            >
                <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
                Torneos
            </Link>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Tabla de <span class="text-primary">Posiciones</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ torneo?.nombre }} — {{ tipoLabel }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <Link
                v-if="can('stats.view')"
                :href="route('estadisticas.index', torneo.id)"
                class="px-4 py-2.5 rounded-xl border-2 border-primary text-primary font-black uppercase text-[10px] tracking-widest hover:bg-primary hover:text-white transition-all"
            >
                <span class="material-symbols-outlined !text-sm mr-1">bar_chart</span>
                Estadísticas
            </Link>
            <button
                @click="descargarPDF"
                class="px-4 py-2.5 rounded-xl bg-white dark:bg-[#1A2C26] border-2 border-primary text-primary font-black uppercase text-[10px] tracking-widest hover:bg-primary hover:text-white transition-all"
            >
                <span class="material-symbols-outlined !text-sm mr-1">picture_as_pdf</span>
                PDF
            </button>
            <button
                v-if="can('standings.recalculate')"
                @click="recalcular"
                class="px-4 py-2.5 rounded-xl bg-primary text-white font-black uppercase text-[10px] tracking-widest shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
            >
                <span class="material-symbols-outlined !text-sm mr-1">refresh</span>
                Recalcular
            </button>
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

    <!-- Toggle vista -->
    <div class="flex flex-wrap gap-2">
        <button
            @click="vista = 'posiciones'"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="vista === 'posiciones'
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            Posiciones
        </button>
        <button
            @click="vista = 'rendimiento'"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="vista === 'rendimiento'
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            Rendimiento
        </button>
    </div>

    <!-- Selector de grupos -->
    <div v-if="grupos?.length > 1" class="flex flex-wrap gap-2">
        <button
            v-for="(grupo, idx) in grupos"
            :key="grupo.id ?? idx"
            @click="grupoActivo = idx"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
            :class="grupoActivo === idx
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
        >
            {{ grupo.nombre }}
        </button>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                <span class="material-symbols-outlined text-xl">leaderboard</span>
            </div>
            <div>
                <h3 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    {{ grupos?.[grupoActivo]?.nombre ?? 'General' }}
                </h3>
                <p class="text-[11px] text-slate-400 font-medium">
                    {{ leyenda }}
                </p>
            </div>
        </div>

        <StandingsTable
            :standings="standingsOrdenados"
            :vista="vista"
            :puede-recalcular="can('standings.recalculate')"
            :torneo-id="torneo.id"
        />
    </div>

</div>
</AuthenticatedLayout>
</template>
