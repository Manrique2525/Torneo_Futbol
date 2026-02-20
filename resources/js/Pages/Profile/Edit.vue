<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>
<template>
    <Head title="Mi Perfil" />

    <AuthenticatedLayout>
        <div class="mb-8 border-b border-slate-200 dark:border-slate-800 pb-6">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white uppercase tracking-tight">
                Configuración del Perfil
            </h2>
            <p class="mt-2 text-base text-slate-500 dark:text-slate-400">
                Gestiona la información de tu cuenta, seguridad y preferencias de acceso.
            </p>
        </div>

        <div class="space-y-8 pb-12">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="h-full"
                />

                <UpdatePasswordForm class="h-full" />

            </div>

            <div class="pt-4">
                <DeleteUserForm />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
