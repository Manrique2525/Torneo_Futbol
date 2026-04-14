<template>
    <Head title="Planes" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-none">
                        Gestión de <span class="text-primary">Planes</span>
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-2">
                        {{ plans.length }} planes configurados · Solo visible para Super Admin
                    </p>
                </div>

                <PrimaryButton @click="openCreate">
                    <span class="material-symbols-outlined !text-sm mr-2">add_card</span>
                    Nuevo Plan
                </PrimaryButton>
            </div>

            <!-- TOASTS para mensajes flash -->
            <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
                <Toast
                    v-if="flashSuccess"
                    :message="flashSuccess"
                    type="success"
                    @close="flashSuccess = null"
                />
                <Toast
                    v-if="flashError"
                    :message="flashError"
                    type="error"
                    @close="flashError = null"
                />
            </div>

            <!-- LISTA DE PLANES o EMPTY STATE -->
            <div v-if="plans.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <Card
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'relative transition-all hover:shadow-md',
                        plan.is_featured ? 'ring-1 ring-primary/30' : ''
                    ]"
                >
                    <Badge
                        v-if="plan.is_featured"
                        variant="success"
                        class="absolute top-4 right-4 z-10"
                    >
                        Popular
                    </Badge>

                    <div
                        v-if="!plan.is_active"
                        class="absolute inset-0 bg-white/60 dark:bg-black/40 z-10 flex items-center justify-center rounded-3xl backdrop-blur-[1px]"
                    >
                        <Badge variant="default">Inactivo</Badge>
                    </div>

                    <div class="p-6">
                        <div class="mb-5">
                            <h3 class="text-lg font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                                {{ plan.name }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ plan.description }}</p>

                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-3xl font-black text-slate-900 dark:text-white">
                                    {{ formatPrice(plan.monthly_price) }}
                                </span>
                                <span class="text-xs text-slate-400 font-bold">/mes</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                {{ formatPrice(plan.annual_price) }}/año
                            </p>
                        </div>

                        <div class="space-y-2 mb-5">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Torneos</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatLimit(plan.max_tournaments) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Equipos</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatLimit(plan.max_teams) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Jugadores</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatLimit(plan.max_players) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Usuarios</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatLimit(plan.max_users) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Almacenamiento</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">
                                    {{ plan.storage_mb >= 1000 ? (plan.storage_mb / 1000) + ' GB' : plan.storage_mb + ' MB' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-5">
                            <Badge
                                v-for="(label, key) in featureLabels"
                                :key="key"
                                :variant="plan[key] ? 'success' : 'default'"
                                class="!rounded-lg text-[9px] font-bold uppercase tracking-wider"
                            >
                                {{ label }}
                            </Badge>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                            <Badge :variant="supportVariant(plan.support_level)">
                                {{ supportLabels[plan.support_level] }}
                            </Badge>
                            <span class="text-[10px] font-bold text-slate-400">
                                {{ plan.tenants_count }} {{ plan.tenants_count === 1 ? 'organización' : 'organizaciones' }}
                            </span>
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex justify-end gap-2">
                            <button
                                @click="openEdit(plan)"
                                class="p-2 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                            >
                                <span class="material-symbols-outlined !text-lg">edit</span>
                            </button>
                            <button
                                @click="confirmDelete(plan)"
                                class="p-2 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                :class="{ 'opacity-30 pointer-events-none': plan.tenants_count > 0 }"
                            >
                                <span class="material-symbols-outlined !text-lg">delete</span>
                            </button>
                        </div>
                    </template>
                </Card>
            </div>

            <EmptyState
                v-else
                title="No hay planes configurados"
                description="Crea el primer plan de precios para tu sistema. Los planes definen los límites y funcionalidades de cada organización."
                icon="sell"
                action-label="Crear Plan"
                @action="openCreate"
            />
        </div>

        <!-- MODAL CREAR/EDITAR -->
        <Modal :show="showFormModal" maxWidth="2xl" @close="showFormModal = false">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-6">
                    {{ editingPlan ? 'Editar' : 'Nuevo' }} <span class="text-primary">Plan</span>
                </h3>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Nombre *</label>
                            <TextInput v-model="form.name" @input="onNameInput" type="text" placeholder="Ej: Pro" class="w-full" />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Slug *</label>
                            <TextInput v-model="form.slug" type="text" placeholder="pro" class="w-full font-mono" />
                            <InputError :message="form.errors.slug" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Descripción</label>
                        <TextInput v-model="form.description" type="text" placeholder="For growing leagues..." class="w-full" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Precios</p>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Precio Mensual *</label>
                                <TextInput v-model="form.monthly_price" type="number" step="0.01" min="0" class="w-full" />
                                <InputError :message="form.errors.monthly_price" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Precio Anual *</label>
                                <TextInput v-model="form.annual_price" type="number" step="0.01" min="0" class="w-full" />
                                <InputError :message="form.errors.annual_price" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Moneda</label>
                                <TextInput v-model="form.currency" type="text" maxlength="3" class="w-full font-mono uppercase" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Límites</p>
                        <p class="text-[9px] text-slate-400 mb-3">Usa <code class="text-primary">-1</code> para ilimitado</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div v-for="field in limitFields" :key="field.key">
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">{{ field.label }}</label>
                                <TextInput v-model="form[field.key]" type="number" min="-1" class="w-full text-center" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Funcionalidades</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label v-for="(label, key) in featureLabels" :key="key"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-white/5">
                                <Checkbox v-model:checked="form[key]" />
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ label }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-1">Soporte</label>
                            <select v-model="form.support_level"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2.5 px-3 text-sm text-slate-700 dark:text-slate-200 outline-none">
                                <option value="basic">Básico</option>
                                <option value="priority">Prioritario</option>
                                <option value="dedicated">Dedicado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-1">Orden</label>
                            <TextInput v-model="form.sort_order" type="number" min="0" class="w-full text-center" />
                        </div>
                        <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl cursor-pointer">
                            <Checkbox v-model:checked="form.is_active" />
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Activo</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl cursor-pointer">
                            <Checkbox v-model:checked="form.is_featured" />
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Destacado</span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <PrimaryButton type="submit" :disabled="form.processing" class="w-full justify-center">
                            {{ form.processing ? 'Guardando...' : (editingPlan ? 'Guardar Cambios' : 'Crear Plan') }}
                        </PrimaryButton>
                        <SecondaryButton type="button" @click="showFormModal = false" class="w-full justify-center">
                            Cancelar
                        </SecondaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL CONFIRMAR ELIMINAR -->
        <Modal :show="showDeleteModal" maxWidth="md" @close="showDeleteModal = false">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter text-slate-900 dark:text-white mb-4">
                    Eliminar <span class="text-red-500">Plan</span>
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                    ¿Eliminar el plan <strong class="text-slate-900 dark:text-white">{{ planToDelete?.name }}</strong>?
                    Solo se puede eliminar si no hay organizaciones usándolo.
                </p>
                <div class="flex flex-col gap-3">
                    <button @click="executeDelete"
                        class="w-full py-3 rounded-2xl text-white font-black uppercase tracking-widest bg-red-600 hover:bg-red-700 transition-all">
                        Confirmar
                    </button>
                    <button @click="showDeleteModal = false"
                        class="py-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import Card from '@/Components/Card.vue'
import Badge from '@/Components/Badge.vue'
import Toast from '@/Components/Toast.vue'
import EmptyState from '@/Components/EmptyState.vue'
import TextInput from '@/Components/TextInput.vue'
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    plans: { type: Array, required: true },
})

const page = usePage()

// Leer flashes de Inertia
const flashSuccess = ref(page.props.flash?.success || null)
const flashError = ref(page.props.flash?.error || null)

// Limpiar flashes del backend para que no se muestren de nuevo (opcional)
if (page.props.flash?.success) delete page.props.flash.success
if (page.props.flash?.error) delete page.props.flash.error

// Estado de modales
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const editingPlan = ref(null)
const planToDelete = ref(null)

// Formulario
const emptyForm = {
    name: '', slug: '', description: '', monthly_price: '', annual_price: '', currency: 'MXN',
    max_tournaments: 2, max_teams: 20, max_players: 300, max_users: 2, max_fields: 5, max_referees: 10, storage_mb: 500,
    has_mobile_app: false, has_streaming: false, has_advanced_stats: false, has_api_access: false,
    has_whatsapp: false, has_reports: false, has_custom_domain: false,
    support_level: 'basic', sort_order: 0, is_active: true, is_featured: false,
}

const form = useForm({ ...emptyForm })

const limitFields = [
    { key: 'max_tournaments', label: 'Torneos' },
    { key: 'max_teams', label: 'Equipos' },
    { key: 'max_players', label: 'Jugadores' },
    { key: 'max_users', label: 'Usuarios' },
    { key: 'max_fields', label: 'Canchas' },
    { key: 'max_referees', label: 'Árbitros' },
    { key: 'storage_mb', label: 'Storage (MB)' },
]

const openCreate = () => {
    editingPlan.value = null
    form.reset()
    Object.assign(form, emptyForm)
    showFormModal.value = true
}

const openEdit = (plan) => {
    editingPlan.value = plan
    Object.keys(emptyForm).forEach(key => {
        form[key] = plan[key] ?? emptyForm[key]
    })
    showFormModal.value = true
}

const submitForm = () => {
    if (editingPlan.value) {
        form.put(route('plans.update', editingPlan.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showFormModal.value = false
                // Actualizar flashes si vienen en la respuesta
                if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
                if (page.props.flash?.error) flashError.value = page.props.flash.error
                // Limpiar después de leerlos
                if (page.props.flash?.success) delete page.props.flash.success
                if (page.props.flash?.error) delete page.props.flash.error
            },
        })
    } else {
        form.post(route('plans.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showFormModal.value = false
                form.reset()
                if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
                if (page.props.flash?.error) flashError.value = page.props.flash.error
                if (page.props.flash?.success) delete page.props.flash.success
                if (page.props.flash?.error) delete page.props.flash.error
            },
        })
    }
}

const onNameInput = () => {
    if (!editingPlan.value) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '')
    }
}

const confirmDelete = (plan) => {
    planToDelete.value = plan
    showDeleteModal.value = true
}

const executeDelete = () => {
    if (!planToDelete.value) return
    router.delete(route('plans.destroy', planToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            planToDelete.value = null
            if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
            if (page.props.flash?.error) flashError.value = page.props.flash.error
            if (page.props.flash?.success) delete page.props.flash.success
            if (page.props.flash?.error) delete page.props.flash.error
        },
    })
}

// Helpers visuales
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 0 }).format(price)
}

const formatLimit = (value) => value === -1 ? '∞' : value.toLocaleString()

const supportLabels = { basic: 'Básico', priority: 'Prioritario', dedicated: 'Dedicado' }

const supportVariant = (level) => {
    const map = { basic: 'default', priority: 'warning', dedicated: 'info' }
    return map[level] || 'default'
}

const featureLabels = {
    has_mobile_app:    'App Móvil',
    has_streaming:     'Streaming',
    has_advanced_stats: 'Estadísticas Avanzadas',
    has_api_access:    'Acceso API',
    has_whatsapp:      'WhatsApp',
    has_reports:       'Reportes',
    has_custom_domain: 'Dominio Personalizado',
}
</script>
