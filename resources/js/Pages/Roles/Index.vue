<script setup>
import { ref, computed, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { useCan } from '@/Shared/Composables/useCan'
import { ROLE_CONFIG, MODULE_LABELS, ACTION_LABELS } from '@/Shared/Constants/roles/roles'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    roles: Array,
    permissions: Object,
})

const { can } = useCan()


// ── State ─────────────────────────
const selectedRole = ref(null)
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const roleToDelete = ref(null)
const saving = ref(false)
const editedPermissions = ref([])

// Sync permisos
watch(selectedRole, (role) => {
    if (role) {
        editedPermissions.value = [...role.permissions]
    }
})

// ── Computed ──────────────────────
const moduleList = computed(() => {
    return Object.entries(props.permissions).map(([module, perms]) => ({
        key: module,
        label: MODULE_LABELS[module] ?? module,
        permissions: perms.map(p => ({
            name: p,
            label: ACTION_LABELS[p.split('.')[1]] ?? p,
        })),
    }))
})

const totalPermissions = computed(() =>
    Object.values(props.permissions).reduce((acc, val) => acc.concat(val), []).length
)

const selectedPermissionCount = computed(() => editedPermissions.value.length)

// ── Permission Logic ──────────────
const isChecked = (perm) => editedPermissions.value.includes(perm)

const togglePermission = (perm) => {
    const idx = editedPermissions.value.indexOf(perm)
    idx >= 0 ? editedPermissions.value.splice(idx, 1) : editedPermissions.value.push(perm)
}

const isModuleFullyChecked = (module) =>
    module.permissions.every(p => editedPermissions.value.includes(p.name))

const toggleModule = (module) => {
    if (selectedRole.value?.is_global) return

    if (isModuleFullyChecked(module)) {
        module.permissions.forEach(p => {
            editedPermissions.value = editedPermissions.value.filter(x => x !== p.name)
        })
    } else {
        module.permissions.forEach(p => {
            if (!editedPermissions.value.includes(p.name)) {
                editedPermissions.value.push(p.name)
            }
        })
    }
}

// ── Save ─────────────────────────
const savePermissions = () => {
    if (!selectedRole.value || selectedRole.value.is_global) return
    saving.value = true
    router.put(route('roles.update', selectedRole.value.id), {
        permissions: editedPermissions.value,
    }, {
        preserveScroll: true,
        onFinish: () => saving.value = false,
    })
}

const hasChanges = computed(() => {
    if (!selectedRole.value) return false
    const original = new Set(selectedRole.value.permissions)
    const current = new Set(editedPermissions.value)
    if (original.size !== current.size) return true
    for (let p of original) {
        if (!current.has(p)) return true
    }
    return false
})

// ── Create Role ──────────────────
const createForm = useForm({
    name: '',
    permissions: [],
})

const isModuleSelectedInCreate = (module) => {
    return module.permissions.every(p => createForm.permissions.includes(p.name))
}

const toggleModuleInCreate = (module) => {
    const allSelected = module.permissions.every(p => createForm.permissions.includes(p.name))
    module.permissions.forEach(p => {
        const idx = createForm.permissions.indexOf(p.name)
        if (allSelected) {
            if (idx >= 0) createForm.permissions.splice(idx, 1)
        } else {
            if (idx === -1) createForm.permissions.push(p.name)
        }
    })
}

const submitCreate = () => {
    createForm.permissions = [...new Set(createForm.permissions)]
    createForm.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

// ── Delete ───────────────────────
const executeDelete = () => {
    if (!roleToDelete.value) return
    router.delete(route('roles.destroy', roleToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            roleToDelete.value = null
            selectedRole.value = null
        },
    })
}

// ── Helpers ──────────────────────
const getRoleBadge = (name) => ROLE_CONFIG[name]?.classes ?? 'bg-slate-100 dark:bg-slate-800 text-slate-500'
const getRoleLabel = (name) => ROLE_CONFIG[name]?.label ?? name
</script>

<template>
    <Head title="Roles y Permisos" />

    <AuthenticatedLayout>
        <div class="flex flex-col h-[calc(100vh-140px)] overflow-hidden">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 flex-shrink-0">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Roles y <span class="text-primary">Permisos</span>
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                        Configuración de accesos y niveles de usuario
                    </p>
                </div>

                <PrimaryButton v-if="can('roles.create')" @click="showCreateModal = true" class="!rounded-2xl !py-3">
                    <span class="material-symbols-outlined !text-sm mr-2">shield_person</span>
                    Nuevo Rol
                </PrimaryButton>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 min-h-0">

                <div class="lg:col-span-4 xl:col-span-3 flex flex-col min-h-0">
                    <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col h-full overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/50 flex-shrink-0">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Listado de Roles</h3>
                        </div>

                        <div class="divide-y divide-slate-50 dark:divide-slate-800/50 overflow-y-auto flex-1 custom-scrollbar">
                            <button
                                v-for="role in roles"
                                :key="role.id"
                                class="w-full flex items-center gap-3 px-5 py-4 text-left transition-all group"
                                :class="{
                                    'bg-primary/5 dark:bg-primary/10 border-l-[3px] border-primary': selectedRole?.id === role.id,
                                    'border-l-[3px] border-transparent hover:bg-slate-50/80 dark:hover:bg-white/5': selectedRole?.id !== role.id,
                                    'opacity-40 cursor-not-allowed': role.is_global && role.name === 'super_admin',
                                }"
                                @click="selectedRole = role"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2" :class="getRoleBadge(role.name)">
                                            {{ getRoleLabel(role.name) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex-1 h-1 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full transition-all duration-500" :style="{ width: `${(role.permissions.length / totalPermissions) * 100}%` }"></div>
                                        </div>
                                        <span class="text-[9px] font-bold text-slate-400 tabular-nums">{{ role.permissions.length }}/{{ totalPermissions }}</span>
                                    </div>
                                </div>
                                <button v-if="can('roles.delete') && !role.is_global" @click.stop="roleToDelete = role; showDeleteModal = true"
                                    class="p-1.5 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-500/10 transition-all opacity-0 group-hover:opacity-100">
                                    <span class="material-symbols-outlined !text-[16px]">delete</span>
                                </button>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 xl:col-span-9 flex flex-col min-h-0">
                    <div v-if="!selectedRole" class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 flex-1 flex flex-col items-center justify-center text-center">
                        <span class="material-symbols-outlined text-6xl text-slate-200 dark:text-slate-700 mb-4">admin_panel_settings</span>
                        <h3 class="text-lg font-black uppercase tracking-tighter text-slate-400">Selecciona un rol</h3>
                    </div>

                    <div v-else class="flex flex-col h-full min-h-0">
                        <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 p-5 flex items-center justify-between mb-4 flex-shrink-0 shadow-sm">
                            <div class="flex items-center gap-4">
                                <span :class="['px-3 py-1.5 rounded-xl text-[11px] font-black uppercase border-b-2', getRoleBadge(selectedRole.name)]">
                                    {{ getRoleLabel(selectedRole.name) }}
                                </span>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    {{ selectedPermissionCount }} de {{ totalPermissions }} permisos
                                </span>
                            </div>
                            <div v-if="!selectedRole.is_global" class="flex gap-4">
                                <button @click="editedPermissions = Object.values(props.permissions).flat()" class="text-[10px] font-black uppercase text-primary hover:underline">Marcar todo</button>
                                <button @click="editedPermissions = []" class="text-[10px] font-black uppercase text-slate-400 hover:underline">Limpiar</button>
                            </div>
                            <div v-else class="text-[10px] font-black uppercase text-amber-500">
                                Rol del Sistema (No editable)
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar pb-24 space-y-4">
                            <div v-for="module in moduleList" :key="module.key" class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                                <button @click="toggleModule(module)"
                                    :disabled="selectedRole.is_global"
                                    class="w-full flex items-center justify-between px-6 py-4 bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                                            :class="isModuleFullyChecked(module) ? 'bg-primary border-primary' : 'border-slate-300 dark:border-slate-600'">
                                            <svg v-if="isModuleFullyChecked(module)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <span class="text-sm font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">{{ module.label }}</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 tabular-nums">
                                        {{ module.permissions.filter(p => isChecked(p.name)).length }} / {{ module.permissions.length }}
                                    </span>
                                </button>

                                <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                                    <label v-for="perm in module.permissions" :key="perm.name"
                                        class="flex items-center gap-3 p-2 rounded-xl transition-all"
                                        :class="[!selectedRole.is_global ? 'hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer' : 'opacity-60']">
                                        <input type="checkbox" :checked="isChecked(perm.name)" @change="togglePermission(perm.name)" :disabled="selectedRole.is_global"
                                            class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20" />
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 leading-tight">{{ perm.label }}</p>
                                            <p class="text-[9px] text-slate-400 font-mono truncate">{{ perm.name }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="hasChanges" class="flex-shrink-0 relative">
                            <div class="absolute bottom-6 left-0 right-0 z-20 px-4">
                                <div class="bg-white dark:bg-[#1A2C26] rounded-3xl border-2 border-primary shadow-2xl p-4 flex items-center justify-between">
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 px-2">Tienes cambios sin guardar</p>
                                    <div class="flex gap-2">
                                        <button @click="editedPermissions = [...selectedRole.permissions]" class="px-4 py-2 text-[10px] font-black uppercase text-slate-400">Descartar</button>
                                        <PrimaryButton @click="savePermissions" :disabled="saving" class="!py-2 !px-6 shadow-lg shadow-primary/20">
                                            {{ saving ? 'Guardando...' : 'Aplicar Cambios' }}
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showCreateModal" maxWidth="md" @close="showCreateModal = false; createForm.reset()">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                    Nuevo <span class="text-primary">Rol</span>
                </h3>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Nombre del Rol (Identificador)</label>
                        <input v-model="createForm.name" type="text" placeholder="ej: supervisor_ventas"
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary dark:text-white" />
                        <InputError :message="createForm.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Selección rápida de módulos</label>
                        <div class="max-h-64 overflow-y-auto border border-slate-100 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-black/10 p-2 custom-scrollbar">
                            <label v-for="module in moduleList" :key="module.key"
                                class="flex items-center justify-between p-3 rounded-xl hover:bg-white dark:hover:bg-white/5 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" :checked="isModuleSelectedInCreate(module)" @change="toggleModuleInCreate(module)"
                                        class="w-4 h-4 rounded border-slate-300 text-primary" />
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ module.label }}</span>
                                </div>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ module.permissions.length }} perms</span>
                            </label>
                        </div>
                        <InputError :message="createForm.errors.permissions" class="mt-2" />
                    </div>

                    <div class="pt-4 flex flex-col gap-2">
                        <PrimaryButton @click="submitCreate" :disabled="createForm.processing" class="w-full !py-4 shadow-xl shadow-primary/20">
                            {{ createForm.processing ? 'Procesando...' : 'Crear Rol' }}
                        </PrimaryButton>
                        <button @click="showCreateModal = false; createForm.reset()" class="py-2 text-[10px] font-black uppercase text-slate-400">Cancelar</button>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteModal" maxWidth="sm" @close="showDeleteModal = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-red-50 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
                </div>
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">¿Confirmar eliminación?</h3>
                <p class="text-xs text-slate-500 font-medium my-4 px-4">Esta acción retirará el acceso a todos los usuarios vinculados a este rol.</p>
                <div class="flex flex-col gap-2">
                    <button @click="executeDelete" class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black uppercase text-[11px] rounded-2xl transition-colors">Eliminar permanentemente</button>
                    <button @click="showDeleteModal = false" class="py-2 text-[10px] font-black uppercase text-slate-400">Cancelar</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-slate-200 dark:bg-slate-800 rounded-full; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { @apply bg-primary/50; }
</style>
