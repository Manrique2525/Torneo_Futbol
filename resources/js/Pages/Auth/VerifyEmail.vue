<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Email" />

        <div class="mb-8 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-primary/10 text-primary mb-4 animate-pulse">
                <span class="material-symbols-outlined !text-4xl">mark_email_read</span>
            </div>
            <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                Casi <span class="text-primary">Listo</span>
            </h2>
            <p class="mt-3 text-[11px] font-bold uppercase tracking-widest text-slate-500 leading-relaxed">
                ¡Gracias por unirte! Antes de empezar la temporada, por favor verifica tu correo haciendo clic en el enlace que te enviamos.
            </p>
        </div>

        <div
            class="mb-6 rounded-2xl bg-green-50 p-4 text-[10px] font-black uppercase tracking-widest text-green-600 border border-green-100 dark:bg-primary/10 dark:text-primary dark:border-primary/20 text-center"
            v-if="verificationLinkSent"
        >
            Se ha enviado un nuevo enlace de verificación a tu correo.
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center py-4 rounded-2xl shadow-lg shadow-primary/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Reenviando...</span>
                    <span v-else>Reenviar Correo de Verificación</span>
                </PrimaryButton>

                <div class="flex items-center justify-center mt-2">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-red-500 transition-colors"
                    >
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
