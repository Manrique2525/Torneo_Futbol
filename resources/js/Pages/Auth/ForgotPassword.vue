<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña" />

        <!-- Header -->
        <div class="mb-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary mb-4">
                <span class="material-symbols-outlined">lock_reset</span>
            </div>

            <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                ¿Problemas con tu <span class="text-primary">Clave</span>?
            </h2>

            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                No te quedes fuera del partido. Te enviaremos un enlace de recuperación.
            </p>
        </div>

        <!-- Mensaje éxito -->
        <div
            v-if="status"
            class="mb-6 rounded-2xl bg-green-50 p-4 text-xs font-black uppercase tracking-widest text-green-600 border border-green-100 dark:bg-primary/10 dark:text-primary dark:border-primary/20"
        >
            {{ status }}
        </div>

        <!-- Formulario -->
        <form @submit.prevent="submit" class="space-y-6">

            <div>
                <InputLabel
                    for="email"
                    value="Correo Electrónico"
                    class="text-[10px] font-black uppercase tracking-widest ml-1"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="tu-email@liga.com"
                />

                <InputError class="mt-2 ml-1" :message="form.errors.email" />
            </div>

            <!-- Botón -->
            <div class="flex flex-col gap-4">

                <PrimaryButton
                    type="submit"
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        </svg>
                        Enviando enlace...
                    </span>

                    <span v-else>
                        Enviar Instrucciones
                    </span>
                </PrimaryButton>

                <Link
                    :href="route('login')"
                    class="text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary transition-colors"
                >
                    Volver al Inicio de Sesión
                </Link>

            </div>
        </form>
    </GuestLayout>
</template>