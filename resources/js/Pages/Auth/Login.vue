<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
    canResetPassword: { type: Boolean, default: false },
    status: { type: String, default: null },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="mb-8 text-center">

            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mt-2">
                Gestión de Liga
            </p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-primary text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@ejemplo.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between px-1">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-300">Recordarme</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-[11px] font-bold uppercase tracking-wider text-primary hover:underline"
                >
                    ¿Olvidaste tu clave?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    type="submit"
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Entrando...
                    </span>
                    <span v-else>Entrar al Panel</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-8 text-center">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                ¿Sin cuenta?
                <Link :href="route('register')" class="text-primary hover:underline ml-1">Regístrate</Link>
            </p>
        </div>
    </GuestLayout>
</template>
