<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    roles: Object
});

const form = useForm({
    name: '',
    email: '',
    perfil: '',
    password: '',
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <Head title="Nuevo Usuario" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <Link :href="route('users.index')" class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary transition-colors mb-6">
                <span class="material-symbols-outlined mr-2 !text-lg">arrow_back</span>
                Volver al listado
            </Link>

            <div class="bg-white dark:bg-[#1A2C26] rounded-[3rem] shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex flex-col md:flex-row">
                    <!-- Panel lateral -->
                    <div class="md:w-1/3 bg-slate-50 dark:bg-black/20 p-10 border-r border-slate-100 dark:border-slate-800">
                        <div class="h-16 w-16 rounded-3xl bg-primary flex items-center justify-center text-white mb-6 shadow-lg shadow-primary/30">
                            <span class="material-symbols-outlined text-3xl">person_add</span>
                        </div>
                        <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white leading-tight">
                            Registrar <br/>
                            <span class="text-primary">Usuario</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-4 leading-relaxed font-medium">
                            Completa los datos para dar acceso a un nuevo integrante de la liga.
                        </p>
                    </div>

                    <!-- Formulario -->
                    <div class="md:w-2/3 p-10">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Nombre Completo</label>
                                <input v-model="form.name" type="text"
                                    class="w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-primary transition-all text-slate-700 dark:text-slate-200"
                                    placeholder="Ej. Juan Pérez">
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Correo Electrónico</label>
                                    <input v-model="form.email" type="email"
                                        class="w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-primary transition-all text-slate-700 dark:text-slate-200"
                                        placeholder="correo@ejemplo.com">
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Perfil / Rol</label>
                                    <select v-model="form.perfil"
                                        class="w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-primary transition-all text-slate-700 dark:text-slate-200 appearance-none">
                                        <option value="" disabled>Seleccionar...</option>
                                        <option v-for="(info, key) in roles" :key="key" :value="info.slug">
                                            {{ key.toUpperCase() }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.perfil" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Contraseña de Acceso</label>
                                <input v-model="form.password" type="password"
                                    class="w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl py-4 px-6 text-sm focus:ring-2 focus:ring-primary transition-all text-slate-700 dark:text-slate-200"
                                    placeholder="••••••••">
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div v-if="form.recentlySuccessful" class="text-green-500 text-xs font-bold animate-pulse">
                                    ¡Datos guardados con éxito!
                                </div>
                                <div v-else></div>

                                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-10 py-4 rounded-2xl shadow-xl shadow-primary/20">
                                    Crear Usuario
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
