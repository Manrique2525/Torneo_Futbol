<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="flex flex-col h-full rounded-2xl border border-slate-200 bg-surface-light p-6 shadow-sm dark:border-slate-800 dark:bg-surface-dark transition-all duration-300">
        <header class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5 dark:border-slate-700/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-sm">
                <span class="material-symbols-outlined text-[28px]">account_circle</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    Profile Information
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Update your personal details.
                </p>
            </div>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="flex-1 flex flex-col">
            <div class="space-y-6 flex-1">
                <div>
                    <InputLabel for="name" value="Full Name" class="mb-2 dark:text-slate-200" />
                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="e.g. Alex Morgan"
                        class="block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email Address" class="mb-2 dark:text-slate-200" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="alex@tournament.com"
                        class="block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div v-if="mustVerifyEmail && user.email_verified_at === null">
                    <div class="flex items-start gap-3 rounded-xl bg-amber-50/50 p-4 border border-amber-200 dark:bg-amber-900/10 dark:border-amber-900/40">
                        <span class="material-symbols-outlined text-amber-600">priority_high</span>
                        <div>
                            <p class="text-sm text-amber-800 dark:text-amber-400">
                                Unverified email.
                                <Link
                                    :href="route('verification.send')"
                                    method="post"
                                    as="button"
                                    class="font-bold underline decoration-2 underline-offset-4 hover:text-amber-900 dark:hover:text-amber-200"
                                >
                                    Resend link.
                                </Link>
                            </p>
                            <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-semibold text-primary">
                                Sent!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 mt-8 pt-6 dark:border-slate-700/50">
                <div class="flex items-center gap-4">
                    <PrimaryButton type="submit" :disabled="form.processing">
                        Save
                    </PrimaryButton>

                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-x-[-10px]"
                        leave-active-class="transition ease-in duration-200"
                        leave-to-class="opacity-0"
                    >
                        <p v-if="form.recentlySuccessful" class="flex items-center gap-2 text-sm font-bold text-primary">
                            <span class="material-symbols-outlined text-[20px]">task_alt</span>
                            Saved
                        </p>
                    </Transition>
                </div>

                <p class="hidden sm:block text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold">
                    Profile Settings
                </p>
            </div>
        </form>
    </section>
</template>
