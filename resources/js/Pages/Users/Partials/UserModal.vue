<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: Boolean,
    user: Object,
    roles: Object
});

const emit = defineEmits(['close']);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    perfil: props.user?.perfil ?? 'espectador',
    password: '',
});

const submit = () => {
    if (props.user) {
        form.put(route('users.update', props.user.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-surface-dark rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
            <div class="p-8">
                <h3 class="text-xl font-black uppercase tracking-tighter mb-6">
                    {{ user ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel value="Nombre" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                        <TextInput v-model="form.name" class="w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel value="Email" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                        <TextInput v-model="form.email" type="email" class="w-full" required />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel value="Perfil de Usuario" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                        <select v-model="form.perfil" class="mt-1 block w-full rounded-2xl border-none bg-slate-50 dark:bg-white/5 py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary">
                            <option v-for="(info, key) in roles" :key="key" :value="info.slug">
                                {{ key.toUpperCase() }} - {{ info.descripcion.substring(0, 30) }}...
                            </option>
                        </select>
                        <InputError :message="form.errors.perfil" />
                    </div>

                    <div v-if="!user">
                        <InputLabel value="Contraseña" class="text-[10px] font-black uppercase tracking-widest ml-1" />
                        <TextInput v-model="form.password" type="password" class="w-full" required />
                    </div>

                    <div class="flex flex-col gap-3 pt-4">
                        <PrimaryButton :disabled="form.processing" class="justify-center py-4" type="submit">
                            {{ user ? 'Actualizar' : 'Crear Usuario' }}
                        </PrimaryButton>
                        <button type="button" @click="$emit('close')" class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
