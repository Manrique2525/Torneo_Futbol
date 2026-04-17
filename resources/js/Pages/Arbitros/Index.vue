<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import VSelectCustom from '@/Components/VSelectCustom.vue';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    arbitros: Object,
    constantes: Object,
    filters: Object,
    flash: Object
});

const constantes = props.constantes || {};

// filtros
const searchQuery = ref(props.filters?.search || '');
const filterNivel = ref(props.filters?.nivel || 'todos');
const filterDisponible = ref(props.filters?.disponible || 'todos');

// options para niveles
const nivelOptions = [
    { label: 'Todos los Niveles', value: 'todos' },
    ...Object.entries(constantes.niveles_arbitro || {}).map(([key, label]) => ({
        label,
        value: key
    }))
];

// options para disponibilidad
const disponibleOptions = [
    { label: 'Todos', value: 'todos' },
    { label: 'Disponibles', value: '1' },
    { label: 'No disponibles', value: '0' }
];

// debounce
const debounce = (fn, delay) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
};

const search = debounce(() => {
    router.get(route('arbitros.index'), {
        search: searchQuery.value,
        nivel: filterNivel.value === 'todos' ? undefined : filterNivel.value,
        disponible: filterDisponible.value === 'todos' ? undefined : filterDisponible.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([searchQuery, filterNivel, filterDisponible], search);

// modal
const confirmModal = ref({ show: false });

const triggerDelete = (arbitro) => {
    confirmModal.value = {
        show: true,
        title: 'Eliminar Árbitro',
        message: `¿Eliminar ${arbitro.nombre}?`,
        action: () => router.delete(route('arbitros.destroy', arbitro.id), { onFinish: closeConfirm })
    };
};

const closeConfirm = () => confirmModal.value.show = false;

// badges
const getNivelBadge = (nivel) => {
    const colors = {
        nacional: 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        regional: 'bg-green-500/10 text-green-600 border-green-500/20',
        local: 'bg-slate-500/10 text-slate-600 border-slate-500/20',
        internacional: 'bg-purple-500/10 text-purple-600 border-purple-500/20',
    };
    return colors[nivel] || 'bg-slate-100 text-slate-500 border-slate-200';
};

const getDisponibleBadge = (disponible) => {
    return disponible 
        ? 'bg-green-500/10 text-green-600 border-green-500/20'
        : 'bg-red-500/10 text-red-600 border-red-500/20';
};
</script>

<template>
<Head title="Árbitros" />

<AuthenticatedLayout>
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                Gestión de <span class="text-primary">Árbitros</span>
            </h2>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                {{ arbitros?.total || 0 }} registros encontrados
            </p>
        </div>

        <Link
            :href="route('arbitros.create')"
            class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
        >
            <span class="material-symbols-outlined !text-sm mr-2">sports_score</span>
            Nuevo Árbitro
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
                placeholder="Buscar árbitro..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all"
            >
        </div>

        <div>
            <VSelectCustom
                v-model="filterNivel"
                :options="nivelOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Seleccionar nivel..."
            />
        </div>

        <div>
            <VSelectCustom
                v-model="filterDisponible"
                :options="disponibleOptions"
                label="label"
                :reduce="opt => opt.value"
                :clearable="false"
                placeholder="Disponibilidad..."
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
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Árbitro</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Contacto</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Nivel</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Disponible</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Pago x Partido</th>
                        <th class="p-6"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <tr
                        v-for="a in arbitros?.data || []"
                        :key="a.id"
                        class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all"
                    >
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                    <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase">
                                        {{ a.nombre.substring(0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ a.nombre }}
                                    </span>
                                 
                                </div>
                            </div>
                        </td>

                        <td class="p-6">
                            <div class="flex flex-col gap-1">
                                <span v-if="a.telefono" class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined !text-sm">phone</span>
                                    {{ a.telefono }}
                                </span>
                                <span v-if="a.email" class="text-xs text-slate-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined !text-xs">mail</span>
                                    {{ a.email }}
                                </span>
                                <span v-if="!a.telefono && !a.email" class="text-sm text-slate-400">
                                    Sin contacto
                                </span>
                            </div>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getNivelBadge(a.nivel)]">
                                {{ constantes.niveles_arbitro?.[a.nivel] || a.nivel }}
                            </span>
                        </td>

                        <td class="p-6 text-center">
                            <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getDisponibleBadge(a.disponible)]">
                                {{ a.disponible ? 'Disponible' : 'No disponible' }}
                            </span>
                        </td>

                        <td class="p-6 text-center">
                            <span v-if="a.pago_por_partido" class="text-sm font-bold text-primary">
                                ${{ Number(a.pago_por_partido).toLocaleString() }}
                            </span>
                            <span v-else class="text-xs text-slate-400">No definido</span>
                        </td>

                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('arbitros.edit', a.id)"
                                    class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">open_in_new</span>
                                </Link>

                                <button
                                    @click="triggerDelete(a)"
                                    class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                >
                                    <span class="material-symbols-outlined !text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!arbitros?.data?.length">
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            No hay árbitros registrados
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Mostrando {{ arbitros.from || 0 }} - {{ arbitros.to || 0 }} de {{ arbitros.total }} registros
                </span>
                <Pagination :links="arbitros.links" />
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