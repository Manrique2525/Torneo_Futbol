<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Object,
    isEditing: Boolean
});

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    perfil: props.user?.perfil ?? '',
    password: '',
});

const submit = () => {
    if (props.isEditing) {
        form.put(route('users.update', props.user.id));
    } else {
        form.post(route('users.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Usuario' : 'Nuevo Usuario'" />

    <AuthenticatedLayout>
        <div class="w-full">

            <!-- Back link con focus verde -->
            <Link
                :href="route('users.index')"
                class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em]
                       text-slate-400 hover:text-primary
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50
                       active:scale-95 active:text-primary
                       transition-all duration-150
                       mb-6 rounded-lg px-2 py-1"
            >
                <span class="material-symbols-outlined mr-2 !text-lg">
                    arrow_back
                </span>
                Volver al listado
            </Link>

            <!-- Card -->
            <div class="bg-white dark:bg-[#1A2C26] rounded-3xl shadow-xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">

                <!-- Card Header -->
                <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="h-10 w-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/30 flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">
                            {{ isEditing ? 'manage_accounts' : 'person_add' }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight text-slate-900 dark:text-white leading-none">
                            {{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}
                        </h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                            {{ isEditing
                                ? 'Modifica los datos o permisos del perfil seleccionado.'
                                : 'Completa los datos para registrar un nuevo acceso.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="submit" class="px-8 py-7">

                    <!-- Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                        <!-- Nombre -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                                Nombre Completo
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                       focus:border-primary focus:ring-2 focus:ring-primary/20
                                       rounded-xl py-3 px-4 text-sm transition-all
                                       text-slate-700 dark:text-slate-200
                                       placeholder-slate-300 dark:placeholder-slate-600
                                       outline-none"
                                placeholder="Ej. Juan Pérez"
                            >
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                                Correo Electrónico
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                       focus:border-primary focus:ring-2 focus:ring-primary/20
                                       rounded-xl py-3 px-4 text-sm transition-all
                                       text-slate-700 dark:text-slate-200
                                       placeholder-slate-300 dark:placeholder-slate-600
                                       outline-none"
                                placeholder="correo@ejemplo.com"
                            >
                            <InputError :message="form.errors.email" class="mt-1.5" />
                        </div>

                        <!-- Rol -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                                Perfil / Rol
                            </label>
                            <select
                                v-model="form.perfil"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                       focus:border-primary focus:ring-2 focus:ring-primary/20
                                       rounded-xl py-3 px-4 text-sm transition-all
                                       text-slate-700 dark:text-slate-200
                                       outline-none appearance-none cursor-pointer"
                            >
                                <option value="" disabled>Seleccionar...</option>
                                <option v-for="(info, key) in roles" :key="key" :value="info.slug">
                                    {{ key.toUpperCase() }}
                                </option>
                            </select>
                            <InputError :message="form.errors.perfil" class="mt-1.5" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">
                                {{ isEditing ? 'Nueva Contraseña (Opcional)' : 'Contraseña de Acceso' }}
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full bg-slate-50 dark:bg-white/5 border border-transparent
                                       focus:border-primary focus:ring-2 focus:ring-primary/20
                                       rounded-xl py-3 px-4 text-sm transition-all
                                       text-slate-700 dark:text-slate-200
                                       outline-none"
                                placeholder="••••••••"
                            >
                            <p v-if="isEditing" class="text-[9px] text-slate-400 mt-1.5 italic font-bold ml-1">
                                Deja en blanco para mantener la actual
                            </p>
                            <InputError :message="form.errors.password" class="mt-1.5" />
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                            <span class="material-symbols-outlined !text-base">check_circle</span>
                            ¡Datos guardados con éxito!
                        </div>
                        <div v-else />

                        <PrimaryButton
                            type="submit"
                            :class="{ 'opacity-50 pointer-events-none': form.processing }"
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl shadow-lg shadow-primary/20 text-sm font-bold uppercase tracking-wider"
                        >
                            {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
                        </PrimaryButton>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>