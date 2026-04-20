<script setup>
import { ref, watch, computed } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import { useCan } from '@/Shared/Composables/useCan'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'
import InputError from '@/Components/InputError.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
    permissions: Object,
    filters: Object,
})

const { can } = useCan()

// ── State ─────────────────────────
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const permissionToDelete = ref(null)
const search = ref(props.filters.search || '')

// ── Search ────────────────────────
watch(search, debounce((value) => {
    router.get(route('permissions.index'), { search: value }, {
        preserveState: true,
        replace: true
    })
}, 300))

// ── Grouped Permissions ───────────
const groupedPermissions = computed(() => {
    const groups = {}

    props.permissions.data.forEach(p => {
        const [module, action] = p.name.includes('.')
            ? p.name.split('.')
            : ['general', p.name]

        if (!groups[module]) groups[module] = []

        groups[module].push({
            ...p,
            action
        })
    })

    return Object.entries(groups).map(([module, perms]) => ({
        module,
        permissions: perms
    }))
})

// ── Form ──────────────────────────
const form = useForm({
    name: '',
})

const submit = () => {
    form.post(route('permissions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false
            form.reset()
        },
    })
}

// ── Delete ────────────────────────
const deletePermission = () => {
    if (!permissionToDelete.value) return

    router.delete(route('permissions.destroy', permissionToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            permissionToDelete.value = null
        },
    })
}
</script>

<template>
    <Head title="Permisos del Sistema" />

    <AuthenticatedLayout>
        <div class="flex flex-col h-[calc(100vh-140px)] overflow-hidden">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 flex-shrink-0 px-4 md:px-0">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Lista de <span class="text-primary">Permisos</span>
                    </h2>

                    <div class="mt-4 relative group">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="BUSCAR PERMISO..."
                            class="bg-slate-100 dark:bg-white/5 border-none rounded-xl text-[10px] font-black tracking-widest pl-10 w-full md:w-64 focus:ring-2 focus:ring-primary uppercase"
                        />
                    </div>
                </div>

                <PrimaryButton
                    v-if="can('permissions.create')"
                    @click="showCreateModal = true"
                    class="!rounded-2xl !py-3">
                    <span class="material-symbols-outlined !text-sm mr-2">key</span>
                    Nuevo Permiso
                </PrimaryButton>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col min-h-0 overflow-hidden mx-4 md:mx-0">

                <!-- Header table -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-black/10">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                        Permisos Agrupados
                    </h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ permissions.total }} totales
                    </span>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">

                    <div v-if="groupedPermissions.length > 0">

                        <div
                            v-for="group in groupedPermissions"
                            :key="group.module"
                            class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm"
                        >

                            <!-- Module Header -->
                            <div class="px-6 py-4 bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800/50 flex justify-between items-center">
                                <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    {{ group.module }}
                                </span>
                                <span class="text-[10px] font-bold text-slate-400">
                                    {{ group.permissions.length }} permisos
                                </span>
                            </div>

                            <!-- Permissions -->
                            <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">

                                <div
                                    v-for="perm in group.permissions"
                                    :key="perm.id"
                                    class="group flex justify-between items-center p-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5"
                                >
                                    <div class="min-w-0">
                                        <p class="text-[9px] font-black text-primary uppercase tracking-tighter">
                                            {{ perm.action }}
                                        </p>
                                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-200 truncate block">
                                            {{ perm.name }}
                                        </span>
                                    </div>

                                    <button
                                        v-if="can('permissions.delete')"
                                        @click="permissionToDelete = perm; showDeleteModal = true"
                                        class="p-2 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-500/10 transition-all opacity-0 group-hover:opacity-100"
                                    >
                                        <span class="material-symbols-outlined !text-[16px]">delete</span>
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">
                            No se encontraron permisos
                        </p>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800/50 bg-slate-50/30 dark:bg-black/5 flex items-center justify-center gap-1">
                    <template v-for="(link, k) in permissions.links" :key="k">
                        <div v-if="link.url === null"
                             class="px-3 py-1 text-[10px] font-black text-slate-400 uppercase"
                             v-html="link.label" />
                        <Link v-else
                              :href="link.url"
                              class="px-3 py-1 text-[10px] font-black uppercase rounded-lg"
                              :class="{
                                  'bg-primary text-white': link.active,
                                  'text-slate-500 hover:bg-slate-200 dark:hover:bg-white/10': !link.active
                              }"
                              v-html="link.label"
                              preserve-scroll />
                    </template>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" maxWidth="md" @close="showCreateModal = false; form.reset()">
            <form @submit.prevent="submit" class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                    Nuevo <span class="text-primary">Permiso</span>
                </h3>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                            Nombre del Permiso
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="ej: ventas.crear"
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm focus:ring-2 focus:ring-primary font-mono"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <PrimaryButton @click="submitCreate" class="w-full !py-4">
                        Crear Permiso
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" maxWidth="sm" @close="showDeleteModal = false">
            <div class="p-8 text-center">
                <h3 class="text-lg font-black">¿Eliminar permiso?</h3>
                <p class="text-xs mt-2">{{ permissionToDelete?.name }}</p>

                <button @click="deletePermission" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-xl">
                    Confirmar
                </button>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
