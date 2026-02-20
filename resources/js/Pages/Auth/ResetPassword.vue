<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer Contraseña" />

        <div class="mb-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary mb-4">
                <span class="material-symbols-outlined">security</span>
            </div>
            <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                Nueva <span class="text-primary">Contraseña</span>
            </h2>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                Asegura tu cuenta de administrador para volver al campo.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Confirmar Usuario" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-100 dark:bg-white/5 border-none rounded-2xl text-slate-500 cursor-not-allowed"
                    v-model="form.email"
                    required
                    readonly
                    autocomplete="username"
                />
                <InputError class="mt-2 ml-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/10 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.password"
                    required
                    autofocus
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2 ml-1" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Repetir Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/10 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2 ml-1" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Actualizando...</span>
                    <span v-else>Guardar Nueva Clave</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
