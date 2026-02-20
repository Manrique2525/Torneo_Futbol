<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};


</script>

<template>
    <GuestLayout>
        <Head title="Registro de Capitán" />

        <div class="mb-8 text-center">

            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mt-2">
                Únete a la plataforma de gestión
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nombre Completo" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Escribe tu nombre"
                />
                <InputError class="mt-2 ml-1" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="email@ejemplo.com"
                />
                <InputError class="mt-2 ml-1" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="password" value="Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2 ml-1" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirmar" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2 ml-1" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="pt-6 space-y-4">
                <PrimaryButton type="submit"
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Inscribiendo...</span>
                    <span v-else>Crear</span>
                </PrimaryButton>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-[11px] font-bold uppercase tracking-wider text-slate-500 hover:text-primary transition-colors"
                    >
                        ¿Ya tienes cuenta? <span class="text-primary underline">Inicia sesión</span>
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
