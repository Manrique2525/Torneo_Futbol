<script setup>
import { ref, watch } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3' // Link añadido aquí
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Modal from '@/Components/Modal.vue'
import InputError from '@/Components/InputError.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
    permissions: Object, // Objeto de paginación de Laravel
    filters: Object,     // Filtros desde el controlador
})

const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const permissionToDelete = ref(null)
const search = ref(props.filters.search || '')

// --- Lógica de Búsqueda ---
watch(search, debounce((value) => {
    router.get(route('permissions.index'), { search: value }, {
        preserveState: true,
        replace: true
    })
}, 300))

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
        onError: () => {
            // Si hay error (ej: duplicado), el modal permanece abierto y muestra el InputError
        }
    })
}

const deletePermission = () => {
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
                            class="bg-slate-100 dark:bg-white/5 border-none rounded-xl text-[10px] font-black tracking-widest pl-10 w-full md:w-64 focus:ring-2 focus:ring-primary transition-all uppercase"
                        />
                    </div>
                </div>

                <PrimaryButton @click="showCreateModal = true" class="!rounded-2xl !py-3">
                    <span class="material-symbols-outlined !text-sm mr-2">key</span>
                    Nuevo Permiso
                </PrimaryButton>
            </div>

            <div class="bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col min-h-0 overflow-hidden mx-4 md:mx-0">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-black/10">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Permisos Registrados</h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ permissions.total }} totales
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                    <div v-if="permissions.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        <div v-for="perm in permissions.data" :key="perm.id"
                            class="group flex justify-between items-center p-4 bg-white dark:bg-[#213a32] border border-slate-100 dark:border-slate-700/50 rounded-2xl hover:border-primary/50 transition-all shadow-sm hover:shadow-md">

                            <div class="min-w-0">
                                <p class="text-[9px] font-black text-primary uppercase tracking-tighter mb-1">
                                    Scope: {{ perm.name.includes('.') ? perm.name.split('.')[0] : 'General' }}
                                </p>
                                <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-200 truncate block">
                                    {{ perm.name }}
                                </span>
                            </div>

                            <button
                                @click="permissionToDelete = perm; showDeleteModal = true"
                                class="p-2 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-500/10 transition-all opacity-0 group-hover:opacity-100">
                                <span class="material-symbols-outlined !text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">No se encontraron permisos</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800/50 bg-slate-50/30 dark:bg-black/5 flex items-center justify-center gap-1">
                    <template v-for="(link, k) in permissions.links" :key="k">
                        <div v-if="link.url === null"
                             class="px-3 py-1 text-[10px] font-black text-slate-400 uppercase tracking-tighter"
                             v-html="link.label" />
                        <Link v-else
                              :href="link.url"
                              class="px-3 py-1 text-[10px] font-black uppercase tracking-tighter rounded-lg transition-all"
                              :class="{
                                  'bg-primary text-white shadow-lg shadow-primary/20': link.active,
                                  'text-slate-500 hover:bg-slate-200 dark:hover:bg-white/10': !link.active
                              }"
                              v-html="link.label"
                              preserve-scroll />
                    </template>
                </div>
            </div>
        </div>

        <Modal :show="showCreateModal" maxWidth="md" @close="showCreateModal = false; form.reset()">
            <form @submit.prevent="submit" class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                    Nuevo <span class="text-primary">Permiso</span>
                </h3>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Nombre del Permiso (Slug)</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="ej: ventas.autorizar"
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary dark:text-white font-mono"
                        />
                        <p class="text-[9px] text-slate-400 mt-2 italic">Formato recomendado: modulo.accion</p>
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="pt-4 flex flex-col gap-2">
                        <PrimaryButton :disabled="form.processing" class="w-full !py-4 shadow-xl shadow-primary/20">
                            {{ form.processing ? 'Registrando...' : 'Crear Permiso' }}
                        </PrimaryButton>

                        <button
                            type="button"
                            @click="showCreateModal = false; form.reset()"
                            class="py-2 text-[10px] font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </Modal>

        <Modal :show="showDeleteModal" maxWidth="sm" @close="showDeleteModal = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-red-50 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
                </div>
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">¿Eliminar Permiso?</h3>
                <p class="text-xs text-slate-500 font-medium my-4 px-4">
                    Esta acción es irreversible para <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">{{ permissionToDelete?.name }}</code>.
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        type="button"
                        @click="deletePermission"
                        class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black uppercase text-[11px] rounded-2xl transition-colors shadow-lg shadow-red-500/20">
                        Confirmar Eliminación
                    </button>
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="py-2 text-[10px] font-black uppercase text-slate-400">
                        Cancelar
                    </button>
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
