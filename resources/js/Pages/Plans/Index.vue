<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import Card from '@/Components/Card.vue'
import Badge from '@/Components/Badge.vue'
import Toast from '@/Components/Toast.vue'
import EmptyState from '@/Components/EmptyState.vue'
import VSelectCustom from '@/Components/VSelectCustom.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    plans: { type: [Array, Object], required: true },
})

const page = usePage()

// Leer flashes de Inertia
const flashSuccess = ref(page.props.flash?.success || null)
const flashError = ref(page.props.flash?.error || null)

// Limpiar flashes del backend para que no se muestren de nuevo
if (page.props.flash?.success) delete page.props.flash.success
if (page.props.flash?.error) delete page.props.flash.error

// Estado del modal de eliminar
const showDeleteModal = ref(false)
const planToDelete = ref(null)

// Computed para manejar tanto array como objeto paginado
const planList = computed(() => {
    if (Array.isArray(props.plans)) return props.plans
    return props.plans.data || []
})

const totalPlanes = computed(() => {
    if (Array.isArray(props.plans)) return props.plans.length
    return props.plans.total || 0
})

const isPaginated = computed(() => {
    return !Array.isArray(props.plans) && props.plans.links !== undefined
})

// ========== FILTROS ==========
const filterStatus = ref('all')
const filterSupport = ref('all')
const filterFeatured = ref('all')
const searchQuery = ref('')

const statusFilterOptions = [
    { label: 'Todos', value: 'all' },
    { label: 'Activo', value: 'active' },
    { label: 'Inactivo', value: 'inactive' },
]
const supportFilterOptions = [
    { label: 'Todos', value: 'all' },
    { label: 'Básico', value: 'basic' },
    { label: 'Prioritario', value: 'priority' },
    { label: 'Dedicado', value: 'dedicated' },
]
const featuredFilterOptions = [
    { label: 'Todos', value: 'all' },
    { label: 'Destacados', value: 'featured' },
    { label: 'No destacados', value: 'not_featured' },
]

const onStatusFilterChange = (opt) => { filterStatus.value = opt?.value ?? 'all' }
const onSupportFilterChange = (opt) => { filterSupport.value = opt?.value ?? 'all' }
const onFeaturedFilterChange = (opt) => { filterFeatured.value = opt?.value ?? 'all' }

// Debounce para filtros
const debounce = (fn, delay) => {
    let t
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), delay) }
}

const performFilter = debounce(() => {
    router.get(route('plans.index'), {
        search: searchQuery.value || undefined,
        status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
        support: filterSupport.value !== 'all' ? filterSupport.value : undefined,
        featured: filterFeatured.value !== 'all' ? (filterFeatured.value === 'featured' ? '1' : '0') : undefined,
    }, { preserveState: true, replace: true })
}, 300)

watch([filterStatus, filterSupport, filterFeatured, searchQuery], performFilter)

// ========== ELIMINAR ==========
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
    has_mobile_app: 'App Móvil',
    has_streaming: 'Streaming',
    has_advanced_stats: 'Estadísticas Avanzadas',
    has_api_access: 'Acceso API',
    has_whatsapp: 'WhatsApp',
    has_reports: 'Reportes',
    has_custom_domain: 'Dominio Personalizado',
}
</script>

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
                        {{ totalPlanes }} planes configurados · Solo visible para Super Admin
                    </p>
                </div>

                <Link :href="route('plans.create')"
                    class="flex items-center px-3 py-3 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                    <span class="material-symbols-outlined !text-sm mr-2">add_card</span>
                    Nuevo Plan
                </Link>
            </div>

            <!-- FILTERS -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 bg-white dark:bg-[#1A2C26] p-4 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="md:col-span-2 relative">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar plan por nombre..."
                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-white/5 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary transition-all text-slate-700 dark:text-slate-200">
                </div>

                <VSelectCustom :modelValue="statusFilterOptions.find(o => o.value === filterStatus)"
                    :options="statusFilterOptions" label="label" :clearable="false" placeholder="Estado"
                    @update:modelValue="onStatusFilterChange" />
                <VSelectCustom :modelValue="supportFilterOptions.find(o => o.value === filterSupport)"
                    :options="supportFilterOptions" label="label" :clearable="false" placeholder="Soporte"
                    @update:modelValue="onSupportFilterChange" />
                <VSelectCustom :modelValue="featuredFilterOptions.find(o => o.value === filterFeatured)"
                    :options="featuredFilterOptions" label="label" :clearable="false" placeholder="Destacado"
                    @update:modelValue="onFeaturedFilterChange" />
            </div>

            <!-- TOASTS para mensajes flash -->
            <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
                <Toast v-if="flashSuccess" :message="flashSuccess" type="success" @close="flashSuccess = null" />
                <Toast v-if="flashError" :message="flashError" type="error" @close="flashError = null" />
            </div>

            <div class="max-h-[calc(100vh-300px)] overflow-y-auto pr-2 custom-scrollbar">
                <!-- LISTA DE PLANES (cards) -->
                <div v-if="planList.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <Card v-for="plan in planList" :key="plan.id" :class="[
                        'relative transition-all hover:shadow-md',
                        plan.is_featured ? 'ring-1 ring-primary/30' : ''
                    ]">
                        <Badge v-if="plan.is_featured" variant="success" class="absolute top-4 right-4 z-10">
                            Popular
                        </Badge>

                        <div v-if="!plan.is_active"
                            class="absolute inset-0 bg-white/60 dark:bg-black/40 z-10 flex items-center justify-center rounded-3xl backdrop-blur-[1px]">
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
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{
                                        formatLimit(plan.max_tournaments) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Equipos</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{
                                        formatLimit(plan.max_teams) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Jugadores</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{
                                        formatLimit(plan.max_players) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Usuarios</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{
                                        formatLimit(plan.max_users) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Almacenamiento</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ plan.storage_mb >= 1000 ? (plan.storage_mb / 1000) + ' GB' : plan.storage_mb + ' MB' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 mb-5">
                                <Badge v-for="(label, key) in featureLabels" :key="key"
                                    :variant="plan[key] ? 'success' : 'default'"
                                    class="!rounded-lg text-[9px] font-bold uppercase tracking-wider">
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
                                <Link :href="route('plans.edit', plan.id)"
                                    class="p-2 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all">
                                    <span class="material-symbols-outlined !text-lg">edit</span>
                                </Link>

                                <button @click="confirmDelete(plan)"
                                    class="p-2 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                                    :class="{ 'opacity-30 pointer-events-none': plan.tenants_count > 0 }">
                                    <span class="material-symbols-outlined !text-lg">delete</span>
                                </button>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Empty State con Link a crear -->
                <EmptyState v-else
                    title="No hay planes configurados"
                    description="Crea el primer plan de precios para tu sistema. Los planes definen los límites y funcionalidades de cada organización."
                    icon="sell">
                    <template #action>
                        <Link :href="route('plans.create')"
                            class="inline-flex items-center px-4 py-2 bg-primary text-white font-black uppercase text-[11px] tracking-[0.15em] rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                            <span class="material-symbols-outlined !text-sm mr-2">add_card</span>
                            Crear Plan
                        </Link>
                    </template>
                </EmptyState>

                <!-- PAGINATION -->
                <div v-if="isPaginated && plans.links?.length"
                    class="flex justify-between items-center p-6 bg-white dark:bg-[#1A2C26] rounded-[2rem] border border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        Mostrando {{ plans.from || 0 }} - {{ plans.to || 0 }} de {{ plans.total }}
                    </span>
                    <Pagination :links="plans.links" />
                </div>
            </div>
        </div>

        <!-- MODAL CONFIRMAR ELIMINAR (se mantiene) -->
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
                    <button type="button" @click="executeDelete"
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
