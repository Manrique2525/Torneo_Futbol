<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    roles: Object,
    filters: Object
});

// --- ESTADOS DE CONFIRMACIÓN ---
const confirmModal = ref({ show: false, title: '', message: '', type: 'primary', action: null });

// --- ESTADOS DE BÚSQUEDA ---
const searchQuery = ref(props.filters.search || '');
const filterRole = ref(props.filters.perfil || 'todos');

// --- DEBOUNCE NATIVA ---
const customDebounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const performSearch = customDebounce(() => {
    router.get(route('users.index'),
        { search: searchQuery.value, perfil: filterRole.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch([searchQuery, filterRole], () => {
    performSearch();
});

// --- ACCIONES ---
const triggerResetPassword = (user) => {
    confirmModal.value = {
        show: true,
        title: '¿Resetear Clave?',
        message: `Se enviará un correo de recuperación a ${user.email}.`,
        type: 'warning',
        action: () => router.post(route('password.email'), { email: user.email }, { onFinish: () => closeConfirm() })
    };
};

const triggerDeleteUser = (user) => {
    confirmModal.value = {
        show: true,
        title: 'Dar de Baja',
        message: `¿Estás seguro de suspender a ${user.name}? Esta acción es reversible.`,
        type: 'danger',
        action: () => router.delete(route('users.destroy', user.id), { onFinish: () => closeConfirm() })
    };
};

const closeConfirm = () => confirmModal.value.show = false;

const getRoleBadge = (slug) => {
    const colors = {
        'administrador': 'bg-red-500/10 text-red-600 border-red-500/20',
        'organizador': 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        'arbitro': 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20',
        'capitan': 'bg-primary/10 text-primary border-primary/20',
    };
    return colors[slug] || 'bg-slate-100 text-slate-500 border-slate-200';
};
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Gestión de <span class="text-primary">Personal</span>
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                        {{ users.total }} registros encontrados
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('users.create')"
                        class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
                    >
                        <span class="material-symbols-outlined !text-sm mr-2">person_add</span>
                        Nuevo Usuario
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="md:col-span-2 relative">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar..." class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all">
                </div>

                <div class="relative">
                    <select v-model="filterRole" class="w-full py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm font-bold text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary appearance-none">
                        <option value="todos">Todos los Perfiles</option>
                        <option v-for="(info, key) in roles" :key="key" :value="info.slug">{{ key.toUpperCase() }}</option>
                    </select>
                </div>

                <div class="flex items-center justify-center bg-primary/5 rounded-2xl border border-dashed border-primary/20">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-primary">Búsqueda Global</span>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Usuario</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Perfil</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                                <th class="p-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            <tr v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                            <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase">
                                                {{ user.name.substring(0, 2) }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col truncate">
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ user.name }}</span>
                                            <span class="text-xs text-slate-500">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getRoleBadge(user.perfil)]">
                                        {{ user.perfil }}
                                    </span>
                                </td>
                                <td class="p-6 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Activo</span>
                                        <span class="text-[9px] uppercase font-bold text-slate-400">Verificado</span>
                                    </div>
                                </td>

                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-2 items-center">
                                        <button @click="triggerResetPassword(user)" title="Resetear Clave" class="p-2.5 rounded-xl bg-orange-500/10 text-orange-600 hover:bg-orange-600 hover:text-white transition-all shadow-sm">
                                            <span class="material-symbols-outlined !text-lg">lock_reset</span>
                                        </button>

                                        <Link :href="route('users.edit', user.id)" title="Ver Perfil Completo" class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all shadow-sm flex items-center justify-center">
                                            <span class="material-symbols-outlined !text-lg">open_in_new</span>
                                        </Link>

                                        <button @click="triggerDeleteUser(user)" title="Dar de Baja" class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                            <span class="material-symbols-outlined !text-lg">person_remove</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100 dark:border-slate-800/50 bg-slate-50/30 dark:bg-black/5">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Mostrando {{ users.from || 0 }} - {{ users.to || 0 }} de {{ users.total }} registros
                        </span>
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación (reset/delete) -->
        <Modal :show="confirmModal.show" maxWidth="md" @close="closeConfirm">
            <div class="p-8">
                <div class="flex items-center gap-5 mb-8">
                    <div :class="[
                        'h-14 w-14 rounded-3xl flex items-center justify-center shrink-0 shadow-lg',
                        confirmModal.type === 'danger' ? 'bg-red-500 text-white shadow-red-500/20' : 'bg-orange-500 text-white shadow-orange-500/20'
                    ]">
                        <span class="material-symbols-outlined text-3xl">
                            {{ confirmModal.type === 'danger' ? 'warning' : 'security' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                            {{ confirmModal.title }}
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Acción del Sistema</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-10 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-slate-800">
                    {{ confirmModal.message }}
                </p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmModal.action" :class="confirmModal.type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-orange-600 hover:bg-orange-700'" class="w-full py-4 rounded-2xl text-white font-black uppercase tracking-[0.2em] text-[11px] transition-all">
                        Confirmar Acción
                    </button>
                    <button @click="closeConfirm" class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Cancelar</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
