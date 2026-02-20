<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-surface-light p-6 shadow-sm dark:border-slate-800 dark:bg-surface-dark transition-colors duration-200">
        <header class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    Update Password
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </div>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 max-w-xl">
                <div>
                    <InputLabel for="current_password" value="Current Password" class="dark:text-slate-200" />

                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="mt-2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />

                    <InputError :message="form.errors.current_password" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="password" value="New Password" class="dark:text-slate-200" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-2"
                        autocomplete="new-password"
                        placeholder="Min. 8 characters"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" class="dark:text-slate-200" />

                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-2"
                        autocomplete="new-password"
                        placeholder="Repeat new password"
                    />

                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/50">
                <PrimaryButton type="submit" :disabled="form.processing">
                    Update Password
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="flex items-center gap-1 text-sm font-medium text-primary"
                    >
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        Password updated.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
