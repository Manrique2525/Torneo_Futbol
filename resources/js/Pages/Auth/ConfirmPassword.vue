<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar Acceso" />

        <div class="mb-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-500 mb-4">
                <span class="material-symbols-outlined !text-3xl">verified_user</span>
            </div>
            <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                Área <span class="text-primary">Protegida</span>
            </h2>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                Por seguridad, confirma tu identidad antes de continuar.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="password" value="Tu Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary transition-all"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="••••••••"
                />

                <InputError class="mt-2 ml-1" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Verificando...</span>
                    <span v-else>Confirmar Identidad</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 text-center">
            <button
                type="button"
                @click="() => window.history.back()"
                class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-primary transition-colors"
            >
                Regresar
            </button>
        </div>
    </GuestLayout>
</template>
