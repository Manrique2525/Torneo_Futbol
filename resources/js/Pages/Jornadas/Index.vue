<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    jornadas: Object,
    torneos: Array,
    constantes: Object,
    filters: Object,
    flash: Object,
});

const constantes = props.constantes || {};

const searchQuery = ref(props.filters?.search || '');
const filterTorneo = ref(props.filters?.torneo_id || 'todos');
const filterEstado = ref(props.filters?.estado || 'todos');

const torneoOptions = [
    { label: 'Todos los Torneos', value: 'todos' },
    ...(props.torneos || []).map((t) => ({
        label: t.nombre,
        value: String(t.id),
    })),
];

const estadoOptions = [
    { label: 'Todos los Estados', value: 'todos' },
    ...Object.entries(constantes.estados_jornada || {}).map(([key, label]) => ({
        label,
        value: key,
    })),
];

const debounce = (fn, delay) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
};

const search = debounce(() => {
    router.get(route('jornadas.index'), {
        search: searchQuery.value,
        torneo_id: filterTorneo.value === 'todos' ? undefined : filterTorneo.value,
        estado: filterEstado.value === 'todos' ? undefined : filterEstado.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([searchQuery, filterTorneo, filterEstado], search);

const confirmModal = ref({ show: false });

const triggerDelete = (jornada) => {
    confirmModal.value = {
        show: true,
        title: 'Eliminar Jornada',
        message: `¿Eliminar ${jornada.nombre}?`,
        action: () => router.delete(route('jornadas.destroy', jornada.id), { onFinish: closeConfirm }),
    };
};

const closeConfirm = () => (confirmModal.value.show = false);

const getEstadoBadge = (estado) => {
    const colors = {
        borrador: 'bg-slate-500/10 text-slate-600 border-slate-500/20',
        programada: 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        finalizada: 'bg-green-500/10 text-green-600 border-green-500/20',
    };
    return colors[estado] || 'bg-slate-100 text-slate-500 border-slate-200';
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
<Head title="Jornadas" />

<AuthenticatedLayout>
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Gestión de <span class="text-primary">Jornadas</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ jornadas?.total || 0 }} registros encontrados
            </p>
        </div>

        <Link
            :href="route('jornadas.create')"
            class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-2">calendar_month</span>
            Nueva Jornada
        </Link>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">

        <div class="md:col-span-2 relative">
            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                <span class="material-symbols-outlined text-xl">search</span>
            </span>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar jornada..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all"
            >
        </div>

        <div>
            <VSelectCustom
                v-model="filterTorneo"
                :options="torneoOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Torneo..."
            />
        </div>

        <div>
            <VSelectCustom
                v-model="filterEstado"
                :options="estadoOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Estado..."
            />
        </div>
    </div>

    <!-- FLASH -->
    <div
        v-if="flash?.success"
        class="bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
    >
        <span class="material-symbols-outlined text-green-600">check_circle</span>
        <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
    </div>

    <!-- TABLA -->
    <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Jornada</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Torneo</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">#</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Fechas</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                        <th class="p-6"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <tr
                        v-for="j in jornadas?.data || []"
                        :key="j.id"
                        class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                    >
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                    <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase">
                                        {{ j.nombre.substring(0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ j.nombre }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="p-6">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined !text-sm text-slate-400">emoji_events</span>
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                    {{ j.torneo?.nombre || '—' }}
                                </span>
                            </div>
                        </td>

                        <td class="p-6 text-center">
                            <span class="text-sm font-bold text-primary">{{ j.numero }}</span>
                        </td>

                        <td class="p-6 text-center">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ formatDate(j.fecha_inicio) }}</span>
                                <span v-if="j.fecha_fin" class="text-[10px] text-slate-400">a {{ formatDate(j.fecha_fin) }}</span>
                            </div>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getEstadoBadge(j.estado)]">
                                {{ constantes.estados_jornada?.[j.estado] || j.estado }}
                            </span>
                        </td>

                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('jornadas.edit', j.id)"
                                    class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">open_in_new</span>
                                </Link>

                                <button
                                    @click="triggerDelete(j)"
                                    class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!jornadas?.data?.length">
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            No hay jornadas registradas
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Mostrando {{ jornadas.from || 0 }} - {{ jornadas.to || 0 }} de {{ jornadas.total }} registros
                </span>
                <Pagination :links="jornadas.links" />
            </div>
        </div>
    </div>

</div>

<!-- MODAL -->
<Modal :show="confirmModal.show" maxWidth="md" @close="closeConfirm">
    <div class="p-8">
        <h3 class="text-xl font-black uppercase tracking-tighter mb-4">
            {{ confirmModal.title }}
        </h3>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            {{ confirmModal.message }}
        </p>
        <div class="flex flex-col gap-3">
            <button
                @click="confirmModal.action"
                class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all"
            >
                Confirmar
            </button>
            <button
                @click="closeConfirm"
                class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400"
            >
                Cancelar
            </button>
        </div>
    </div>
</Modal>

</AuthenticatedLayout>
</template>
