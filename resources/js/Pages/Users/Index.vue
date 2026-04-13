<script setup>
import { ref, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'
import VSelectCustom from '@/Components/VSelectCustom.vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { useCan } from '@/Shared/Composables/useCan'
import { ROLE_CONFIG } from '@/Shared/Constants/roles/roles'

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
})

const { can } = useCan()

// Role options for filter
const roleFilterOptions = [
    { label: 'Todos los Roles', value: 'all' },
    ...props.roles.map(r => ({ label: r.label, value: r.value })),
]

// Status options for filter
const statusOptions = [
    { label: 'Todos', value: 'all' },
    { label: 'Activo', value: 'active' },
    { label: 'Inactivo', value: 'inactive' },
    { label: 'Suspendido', value: 'suspended' },
]

// Confirm modal
const confirmModal = ref({ show: false, title: '', message: '', action: null })

// Search & filters
const searchQuery = ref(props.filters?.search || '')
const filterRole = ref(props.filters?.role || 'all')
const filterStatus = ref(props.filters?.status || 'all')

// Debounce
const debounce = (fn, delay) => {
    let t
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), delay) }
}

const performSearch = debounce(() => {
    router.get(route('users.index'), {
        search: searchQuery.value || undefined,
        role: filterRole.value !== 'all' ? filterRole.value : undefined,
        status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
    }, { preserveState: true, replace: true })
}, 300)

watch([searchQuery, filterRole, filterStatus], performSearch)

const onRoleFilterChange = (opt) => { filterRole.value = opt?.value ?? 'all' }
const onStatusFilterChange = (opt) => { filterStatus.value = opt?.value ?? 'all' }

// Actions
const triggerDelete = (user) => {
    confirmModal.value = {
        show: true,
        title: 'Dar de Baja',
        message: `¿Eliminar a ${user.name}? Esta acción es reversible.`,
        action: () => router.delete(route('users.destroy', user.id), {
            onFinish: () => confirmModal.value.show = false,
        }),
    }
}

// Role badge
const getRoleBadge = (roles) => {
    const role = roles?.[0] ?? ''
    return ROLE_CONFIG[role]?.classes ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
}

const getRoleLabel = (roles) => {
    const role = roles?.[0] ?? ''
    return ROLE_CONFIG[role]?.label ?? role
}

// Status badge
const getStatusBadge = (status) => {
    const map = {
        active:    'text-emerald-600 dark:text-emerald-400',
        inactive:  'text-slate-400',
        suspended: 'text-red-500',
    }
    return map[status] ?? 'text-slate-400'
}

const getStatusLabel = (status) => {
    const map = { active: 'Activo', inactive: 'Inactivo', suspended: 'Suspendido' }
    return map[status] ?? status
}
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Gestión de <span class="text-primary">Usuarios</span>
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                        {{ users.total }} registros encontrados
                    </p>
                </div>

                <Link
                    v-if="can('users.create')"
                    :href="route('users.create')"
                    class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined !text-sm mr-2">person_add</span>
                    Nuevo Usuario
                </Link>
            </div>

            <!-- FILTERS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="md:col-span-2 relative">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar por nombre o email..."
                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all">
                </div>

                <div>
                    <VSelectCustom
                        :modelValue="roleFilterOptions.find(o => o.value === filterRole)"
                        :options="roleFilterOptions"
                        label="label"
                        :clearable="false"
                        :append-to-body="true"
                        placeholder="Filtrar por rol..."
                        @update:modelValue="onRoleFilterChange"
                    />
                </div>

                <div>
                    <VSelectCustom
                        :modelValue="statusOptions.find(o => o.value === filterStatus)"
                        :options="statusOptions"
                        label="label"
                        :clearable="false"
                        :append-to-body="true"
                        placeholder="Estado..."
                        @update:modelValue="onStatusFilterChange"
                    />
                </div>
            </div>

            <!-- FLASH -->
            <div v-if="$page.props.flash?.success"
                class="bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ $page.props.flash.success }}</span>
            </div>

            <!-- TABLE -->
            <div class="bg-white dark:bg-[#1A2C26] rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Usuario</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Rol</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Estado</th>
                                <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Registro</th>
                                <th class="p-6"></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            <tr v-for="user in users.data" :key="user.id"
                                class="group hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all">

                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary to-green-600 p-[2px] shrink-0">
                                            <div class="h-full w-full rounded-[14px] bg-white dark:bg-[#1A2C26] flex items-center justify-center font-black text-primary uppercase">
                                                {{ user.name.substring(0, 2) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ user.name }}</span>
                                            <p class="text-xs text-slate-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-6">
                                    <span :class="['px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2', getRoleBadge(user.roles)]">
                                        {{ getRoleLabel(user.roles) }}
                                    </span>
                                </td>

                                <td class="p-6 text-center">
                                    <span :class="['text-[11px] font-bold', getStatusBadge(user.status)]">
                                        {{ getStatusLabel(user.status) }}
                                    </span>
                                </td>

                                <td class="p-6 text-center">
                                    <span class="text-xs text-slate-400">{{ user.created_at }}</span>
                                </td>

                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link v-if="can('users.update')"
                                            :href="route('users.edit', user.id)"
                                            class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all">
                                            <span class="material-symbols-outlined !text-lg">open_in_new</span>
                                        </Link>

                                        <button v-if="can('users.delete')"
                                            @click="triggerDelete(user)"
                                            class="p-2.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                            <span class="material-symbols-outlined !text-lg">person_remove</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="p-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-3 block">group_off</span>
                                    <p class="text-sm font-bold text-slate-400">No se encontraron usuarios</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100 dark:border-slate-800/50">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Mostrando {{ users.from || 0 }} - {{ users.to || 0 }} de {{ users.total }}
                        </span>
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <Modal :show="confirmModal.show" maxWidth="md" @close="confirmModal.show = false">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter mb-4">{{ confirmModal.title }}</h3>
                <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">{{ confirmModal.message }}</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmModal.action"
                        class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all">
                        Confirmar
                    </button>
                    <button @click="confirmModal.show = false"
                        class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                        Cancelar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
