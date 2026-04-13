<script setup>
import { ref, computed, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { useCan } from '@/Shared/Composables/useCan'
import { ROLE_CONFIG, MODULE_LABELS, ACTION_LABELS } from '@/Shared/Constants/roles/roles'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal         from '@/Components/Modal.vue'
import InputError    from '@/Components/InputError.vue'

const props = defineProps({
    roles:       { type: Array, required: true },
    permissions: { type: Object, required: true },
})

const { can } = useCan()

// ── State ──────────────────────────────────────────
const selectedRole     = ref(null)
const showCreateModal  = ref(false)
const showDeleteModal  = ref(false)
const roleToDelete     = ref(null)
const saving           = ref(false)

// ── Selected role's permissions (editable) ─────────
const editedPermissions = ref([])

watch(selectedRole, (role) => {
    if (role) {
        editedPermissions.value = [...role.permissions]
    }
})

// ── Computed ────────────────────────────────────────
const moduleList = computed(() => {
    return Object.entries(props.permissions).map(([module, perms]) => ({
        key: module,
        label: MODULE_LABELS[module] ?? module,
        permissions: perms.map(p => {
            const action = p.split('.')[1] ?? p
            return {
                name: p,
                label: ACTION_LABELS[action] ?? action,
            }
        }),
    }))
})

const permissionCount = (role) => role.permissions?.length ?? 0

const totalPermissions = computed(() => Object.values(props.permissions).flat().length)

const selectedPermissionCount = computed(() => editedPermissions.value.length)

// ── Permission toggles ─────────────────────────────
const isChecked = (permName) => editedPermissions.value.includes(permName)

const togglePermission = (permName) => {
    const idx = editedPermissions.value.indexOf(permName)
    if (idx >= 0) editedPermissions.value.splice(idx, 1)
    else editedPermissions.value.push(permName)
}

const isModuleFullyChecked = (module) => {
    return module.permissions.every(p => editedPermissions.value.includes(p.name))
}

const isModulePartiallyChecked = (module) => {
    const checked = module.permissions.filter(p => editedPermissions.value.includes(p.name)).length
    return checked > 0 && checked < module.permissions.length
}

const toggleModule = (module) => {
    if (isModuleFullyChecked(module)) {
        module.permissions.forEach(p => {
            const idx = editedPermissions.value.indexOf(p.name)
            if (idx >= 0) editedPermissions.value.splice(idx, 1)
        })
    } else {
        module.permissions.forEach(p => {
            if (!editedPermissions.value.includes(p.name)) editedPermissions.value.push(p.name)
        })
    }
}

const selectAll = () => { editedPermissions.value = Object.values(props.permissions).flat() }
const deselectAll = () => { editedPermissions.value = [] }

// ── Actions ─────────────────────────────────────────
const selectRole = (role) => {
    if (role.name === 'super_admin') return
    selectedRole.value = role
}

const savePermissions = () => {
    if (!selectedRole.value || selectedRole.value.is_global) return
    saving.value = true
    router.put(route('roles.update', selectedRole.value.id), {
        permissions: editedPermissions.value,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false },
    })
}

const hasChanges = computed(() => {
    if (!selectedRole.value) return false
    const original = [...selectedRole.value.permissions].sort()
    const current  = [...editedPermissions.value].sort()
    return JSON.stringify(original) !== JSON.stringify(current)
})

// ── Create role ─────────────────────────────────────
const createForm = useForm({ name: '', permissions: [] })

const submitCreate = () => {
    createForm.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => { showCreateModal.value = false; createForm.reset() },
    })
}

// ── Delete role ─────────────────────────────────────
const confirmDelete = (role) => { roleToDelete.value = role; showDeleteModal.value = true }

const executeDelete = () => {
    if (!roleToDelete.value) return
    router.delete(route('roles.destroy', roleToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            if (selectedRole.value?.id === roleToDelete.value?.id) selectedRole.value = null
            roleToDelete.value = null
        },
    })
}

// ── Role helpers ────────────────────────────────────
const roleBadgeClasses = (roleName) => {
    return ROLE_CONFIG[roleName]?.classes ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
}

const roleLabel = (roleName) => {
    return ROLE_CONFIG[roleName]?.label ?? roleName
}
</script>

<template>
    <Head title="Roles y Permisos" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- ═══ HEADER ═══ -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Roles y <span class="text-primary">Permisos</span>
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                        {{ roles.length }} roles configurados · {{ totalPermissions }} permisos disponibles
                    </p>
                </div>

                <button
                    v-if="can('roles.create')"
                    @click="showCreateModal = true"
                    class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined !text-sm mr-2">shield_person</span>
                    Nuevo Rol
                </button>
            </div>

            <!-- ═══ MAIN GRID ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT: Role list -->
                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/50">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                Roles del Sistema
                            </h3>
                        </div>

                        <div class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            <button
                                v-for="role in roles"
                                :key="role.id"
                                class="w-full flex items-center gap-3 px-5 py-4 text-left transition-all"
                                :class="{
                                    'bg-primary/5 dark:bg-primary/10 border-l-[3px] border-primary': selectedRole?.id === role.id,
                                    'border-l-[3px] border-transparent hover:bg-slate-50/80 dark:hover:bg-white/5': selectedRole?.id !== role.id,
                                    'opacity-40 cursor-not-allowed': role.name === 'super_admin',
                                }"
                                @click="selectRole(role)"
                                :disabled="role.name === 'super_admin'"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-tighter border-b-2"
                                            :class="roleBadgeClasses(role.name)"
                                        >
                                            {{ roleLabel(role.name) }}
                                        </span>
                                        <span
                                            v-if="role.is_global"
                                            class="text-[8px] px-1.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-400 font-black uppercase tracking-widest"
                                        >
                                            Global
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex-1 h-1 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-primary rounded-full transition-all duration-500"
                                                :style="{ width: `${(permissionCount(role) / totalPermissions) * 100}%` }"
                                            ></div>
                                        </div>
                                        <span class="text-[9px] font-bold text-slate-400 tabular-nums">
                                            {{ permissionCount(role) }}/{{ totalPermissions }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    v-if="can('roles.delete') && !role.is_global"
                                    class="p-1.5 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-500/10 dark:text-slate-600 dark:hover:text-red-400 transition-all"
                                    @click.stop="confirmDelete(role)"
                                >
                                    <span class="material-symbols-outlined !text-[16px]">delete</span>
                                </button>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Permission matrix -->
                <div class="lg:col-span-8 xl:col-span-9">

                    <!-- Empty state -->
                    <div
                        v-if="!selectedRole"
                        class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col items-center justify-center py-24 text-center"
                    >
                        <div class="h-20 w-20 rounded-3xl bg-slate-100 dark:bg-white/5 flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">
                                admin_panel_settings
                            </span>
                        </div>
                        <h3 class="text-lg font-black uppercase tracking-tighter text-slate-400">
                            Selecciona un rol
                        </h3>
                        <p class="text-xs text-slate-400 mt-2 max-w-xs">
                            Haz clic en un rol de la lista para ver y editar sus permisos.
                        </p>
                    </div>

                    <!-- Permission editor -->
                    <div v-else class="space-y-4">

                        <!-- Header bar -->
                        <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-5">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-[11px] font-black uppercase tracking-tighter border-b-2"
                                        :class="roleBadgeClasses(selectedRole.name)"
                                    >
                                        {{ roleLabel(selectedRole.name) }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-400 tabular-nums">
                                        {{ selectedPermissionCount }} / {{ totalPermissions }} permisos
                                    </span>
                                </div>
                                <div v-if="!selectedRole.is_global" class="flex items-center gap-3">
                                    <button
                                        class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-dark transition-colors"
                                        @click="selectAll"
                                    >
                                        Marcar todos
                                    </button>
                                    <span class="text-slate-200 dark:text-slate-700">|</span>
                                    <button
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                        @click="deselectAll"
                                    >
                                        Desmarcar
                                    </button>
                                </div>
                            </div>

                            <!-- Read-only warning -->
                            <div
                                v-if="selectedRole.is_global"
                                class="mt-4 flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20"
                            >
                                <span class="material-symbols-outlined text-amber-500 !text-lg">info</span>
                                <p class="text-[11px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">
                                    Rol global — Solo lectura. Crea un rol personalizado para modificar permisos.
                                </p>
                            </div>
                        </div>

                        <!-- Permission modules -->
                        <div
                            v-for="module in moduleList"
                            :key="module.key"
                            class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden"
                        >
                            <!-- Module header -->
                            <button
                                class="w-full flex items-center justify-between px-6 py-4 bg-slate-50/50 dark:bg-black/10 border-b border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-black/20 transition-all"
                                :disabled="selectedRole.is_global"
                                @click="toggleModule(module)"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                                        :class="{
                                            'bg-primary border-primary': isModuleFullyChecked(module),
                                            'border-primary bg-primary/20': isModulePartiallyChecked(module),
                                            'border-slate-300 dark:border-slate-600': !isModuleFullyChecked(module) && !isModulePartiallyChecked(module),
                                        }"
                                    >
                                        <svg v-if="isModuleFullyChecked(module)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <div v-else-if="isModulePartiallyChecked(module)" class="w-2.5 h-0.5 bg-primary rounded-full"></div>
                                    </div>

                                    <span class="text-sm font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                        {{ module.label }}
                                    </span>
                                </div>

                                <span class="text-[10px] font-bold text-slate-400 tabular-nums">
                                    {{ module.permissions.filter(p => isChecked(p.name)).length }} / {{ module.permissions.length }}
                                </span>
                            </button>

                            <!-- Individual permissions -->
                            <div class="px-5 py-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1">
                                <label
                                    v-for="perm in module.permissions"
                                    :key="perm.name"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all"
                                    :class="{
                                        'hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer': !selectedRole.is_global,
                                        'cursor-not-allowed opacity-50': selectedRole.is_global,
                                    }"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isChecked(perm.name)"
                                        :disabled="selectedRole.is_global"
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary/50 dark:bg-slate-700 transition-all"
                                        @change="togglePermission(perm.name)"
                                    />
                                    <div class="min-w-0">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {{ perm.label }}
                                        </span>
                                        <p class="text-[9px] text-slate-400 dark:text-slate-500 font-mono truncate">
                                            {{ perm.name }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Sticky save bar -->
                        <div v-if="!selectedRole.is_global && can('roles.update')" class="sticky bottom-4 z-10">
                            <transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 translate-y-4"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-4"
                            >
                                <div
                                    v-if="hasChanges"
                                    class="flex items-center justify-between p-5 bg-white dark:bg-[#1A2C26] rounded-[2rem] border-2 border-primary/20 shadow-xl shadow-primary/10"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-primary animate-pulse">save</span>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                            Cambios sin guardar en
                                            <span class="text-primary">{{ roleLabel(selectedRole.name) }}</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="editedPermissions = [...selectedRole.permissions]"
                                            class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                                        >
                                            Descartar
                                        </button>
                                        <button
                                            @click="savePermissions"
                                            :disabled="saving"
                                            class="flex items-center px-5 py-2.5 bg-primary text-white font-black uppercase text-[10px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50"
                                        >
                                            <span v-if="saving" class="material-symbols-outlined !text-sm mr-2 animate-spin">progress_activity</span>
                                            <span v-else class="material-symbols-outlined !text-sm mr-2">check</span>
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ CREATE ROLE MODAL ═══ -->
        <Modal :show="showCreateModal" maxWidth="md" @close="showCreateModal = false; createForm.reset()">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                    Nuevo <span class="text-primary">Rol</span>
                </h3>

                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                            Nombre del rol *
                        </label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            placeholder="ej: field_coordinator"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all"
                        />
                        <p class="text-[9px] text-slate-400 mt-1.5">
                            Solo letras minúsculas y guiones bajos. Ej: <code class="text-primary">field_coordinator</code>
                        </p>
                        <InputError :message="createForm.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                            Permisos iniciales
                        </label>
                        <p class="text-[9px] text-slate-400 mb-3">
                            Selecciona módulos completos. Podrás ajustar permisos individuales después.
                        </p>
                        <div class="max-h-60 overflow-y-auto space-y-1 rounded-2xl border border-slate-100 dark:border-slate-800 p-3 bg-slate-50/50 dark:bg-black/10">
                            <label
                                v-for="module in moduleList"
                                :key="module.key"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white dark:hover:bg-white/5 cursor-pointer transition-all"
                            >
                                <input
                                    type="checkbox"
                                    :checked="module.permissions.every(p => createForm.permissions.includes(p.name))"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary/50 dark:bg-slate-700"
                                    @change="
                                        module.permissions.forEach(p => {
                                            const idx = createForm.permissions.indexOf(p.name)
                                            if ($event.target.checked && idx === -1) createForm.permissions.push(p.name)
                                            if (!$event.target.checked && idx >= 0) createForm.permissions.splice(idx, 1)
                                        })
                                    "
                                />
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ module.label }}</span>
                                <span class="text-[9px] font-bold text-slate-400 ml-auto">{{ module.permissions.length }}</span>
                            </label>
                        </div>
                        <InputError :message="createForm.errors.permissions" class="mt-1" />
                    </div>
                </div>

                <div class="flex flex-col gap-3 mt-8">
                    <button
                        @click="submitCreate"
                        :disabled="createForm.processing"
                        class="w-full py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-95 transition-all disabled:opacity-50"
                    >
                        <span v-if="createForm.processing">Creando...</span>
                        <span v-else>Crear Rol</span>
                    </button>
                    <button
                        @click="showCreateModal = false; createForm.reset()"
                        class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ═══ DELETE ROLE MODAL ═══ -->
        <Modal :show="showDeleteModal" maxWidth="md" @close="showDeleteModal = false; roleToDelete = null">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-4">
                    Eliminar <span class="text-red-500">Rol</span>
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                    ¿Eliminar el rol
                    <strong class="text-slate-900 dark:text-white">{{ roleToDelete ? roleLabel(roleToDelete.name) : '' }}</strong>?
                    Los usuarios con este rol perderán todos sus permisos asociados.
                </p>

                <div class="flex flex-col gap-3">
                    <button
                        @click="executeDelete"
                        class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all"
                    >
                        Confirmar Eliminación
                    </button>
                    <button
                        @click="showDeleteModal = false; roleToDelete = null"
                        class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
