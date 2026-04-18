<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, useForm, Link, router  } from '@inertiajs/vue3'

const props = defineProps({
    plan: Object,
})

const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description,
    monthly_price: props.plan.monthly_price,
    annual_price: props.plan.annual_price,
    currency: props.plan.currency,
    max_tournaments: props.plan.max_tournaments,
    max_teams: props.plan.max_teams,
    max_players: props.plan.max_players,
    max_users: props.plan.max_users,
    max_fields: props.plan.max_fields,
    max_referees: props.plan.max_referees,
    storage_mb: props.plan.storage_mb,
    has_mobile_app: props.plan.has_mobile_app,
    has_streaming: props.plan.has_streaming,
    has_advanced_stats: props.plan.has_advanced_stats,
    has_api_access: props.plan.has_api_access,
    has_whatsapp: props.plan.has_whatsapp,
    has_reports: props.plan.has_reports,
    has_custom_domain: props.plan.has_custom_domain,
    support_level: props.plan.support_level,
    sort_order: props.plan.sort_order,
    is_active: props.plan.is_active,
    is_featured: props.plan.is_featured,
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

const submit = () => {
    form.put(route('plans.update', props.plan.id), {
        onSuccess: () => {
            router.visit(route('plans.index'))
        },
    })
}
</script>

<template>
    <Head title="Editar Plan" />

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
                        <span class="material-symbols-outlined text-xl">edit</span>
                    </div>
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white leading-none">Editar Plan</h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Modifica los límites y características del plan.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="px-8 py-7 space-y-6">
                    <!-- Nombre y Slug -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Nombre *</label>
                            <TextInput v-model="form.name" class="w-full" />
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Slug *</label>
                            <TextInput v-model="form.slug" class="w-full font-mono" />
                            <InputError :message="form.errors.slug" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Descripción</label>
                        <TextInput v-model="form.description" class="w-full" />
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
                            ¡Plan actualizado con éxito!
                        </div>
                        <div v-else />
                        <PrimaryButton type="submit" :disabled="form.processing" class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider">
                            Guardar Cambios
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
