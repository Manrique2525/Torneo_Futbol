<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import ModalHeader from '@/Components/ModalHeader.vue'; // Importalo aquí
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="rounded-2xl border border-red-100 bg-red-50/30 p-6 shadow-sm dark:border-red-900/30 dark:bg-red-900/10 transition-all duration-300">
        <header class="mb-6 flex items-center gap-3 border-b border-red-100 pb-4 dark:border-red-900/20">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Delete Account</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Permanently remove your account and all associated data.</p>
            </div>
        </header>

        <div class="max-w-xl text-sm text-slate-600 dark:text-slate-400">
            Once your account is deleted, all of its resources and data will be permanently deleted.
        </div>

        <div class="mt-5">
            <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal" maxWidth="md">
            <ModalHeader
                title="Confirm Deletion"
                subtitle="Destructive Action"
                @close="closeModal"
            />

            <div class="p-6">
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                    This action cannot be undone. Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <div>
                    <InputLabel for="password" value="Password" class="sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Confirm with your password"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirm Deletion
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
