<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, useForm, Link, router  } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    slug: '',
    description: '',
    monthly_price: '',
    annual_price: '',
    currency: 'MXN',
    max_tournaments: 2,
    max_teams: 20,
    max_players: 300,
    max_users: 2,
    max_fields: 5,
    max_referees: 10,
    storage_mb: 500,
    has_mobile_app: false,
    has_streaming: false,
    has_advanced_stats: false,
    has_api_access: false,
    has_whatsapp: false,
    has_reports: false,
    has_custom_domain: false,
    support_level: 'basic',
    sort_order: 0,
    is_active: true,
    is_featured: false,
})

const limitFields = [
    { key: 'max_tournaments', label: 'Torneos' },
    { key: 'max_teams', label: 'Equipos' },
    { key: 'max_players', label: 'Jugadores' },
    { key: 'max_users', label: 'Usuarios' },
    { key: 'max_fields', label: 'Canchas' },
    { key: 'max_referees', label: 'Árbitros' },
    { key: 'storage_mb', label: 'Storage (MB)' },
]

const featureLabels = {
    has_mobile_app: 'App Móvil',
    has_streaming: 'Streaming',
    has_advanced_stats: 'Estadísticas Avanzadas',
    has_api_access: 'Acceso API',
    has_whatsapp: 'WhatsApp',
    has_reports: 'Reportes',
    has_custom_domain: 'Dominio Personalizado',
}

const onNameInput = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '')
}

const submit = () => {
    form.post(route('plans.store'), {
        onSuccess: () => {
            // Redirigir al índice después de guardar exitosamente
            router.visit(route('plans.index'))
        },
    })
}
</script>

<template>
    <Head title="Nuevo Plan" />

    <AuthenticatedLayout>
        <div class="w-full">
            <Link
                :href="route('plans.index')"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary transition-all mb-6 rounded-lg px-2 py-1"
            >
                <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
                Volver al listado
            </Link>

            <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30">
                        <span class="material-symbols-outlined text-xl">add_card</span>
                    </div>
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white leading-none">Nuevo Plan</h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Define un nuevo plan de precios y sus límites.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="px-8 py-7 space-y-6">
                    <!-- Nombre y Slug -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Nombre *</label>
                            <TextInput v-model="form.name" @input="onNameInput" placeholder="Ej: Pro" class="w-full" />
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Slug *</label>
                            <TextInput v-model="form.slug" placeholder="pro" class="w-full font-mono" />
                            <InputError :message="form.errors.slug" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Descripción</label>
                        <TextInput v-model="form.description" placeholder="For growing leagues..." class="w-full" />
                    </div>

                    <!-- Precios -->
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Precios</p>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Precio Mensual *</label>
                                <TextInput v-model="form.monthly_price" type="number" step="0.01" min="0" class="w-full" />
                                <InputError :message="form.errors.monthly_price" class="mt-1.5" />
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Precio Anual *</label>
                                <TextInput v-model="form.annual_price" type="number" step="0.01" min="0" class="w-full" />
                                <InputError :message="form.errors.annual_price" class="mt-1.5" />
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Moneda</label>
                                <TextInput v-model="form.currency" maxlength="3" class="w-full font-mono uppercase" />
                            </div>
                        </div>
                    </div>

                    <!-- Límites -->
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

                    <!-- Funcionalidades -->
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Funcionalidades</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label v-for="(label, key) in featureLabels" :key="key"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5">
                                <Checkbox v-model:checked="form[key]" />
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ label }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Soporte, orden, activo, destacado -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-1">Soporte</label>
                            <select v-model="form.support_level"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl py-2.5 px-3 text-sm outline-none">
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

                    <!-- Botones -->
                    <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                            <span class="material-symbols-outlined !text-base">check_circle</span>
                            ¡Plan creado con éxito!
                        </div>
                        <div v-else />
                        <PrimaryButton type="submit" :disabled="form.processing" class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider">
                            Crear Plan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
