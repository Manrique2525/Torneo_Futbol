<script setup>
defineProps({
    show: Boolean,
    team: Object
});

defineEmits(['close']);

const stats = [
    { name: 'Goleadores', icon: 'sports_soccer', list: [
        { name: 'Karim B.', value: 12, sub: 'Goles' },
        { name: 'Vinicius J.', value: 8, sub: 'Goles' }
    ]},
    { name: 'Asistencias', icon: 'magic_button', list: [
        { name: 'Luka M.', value: 10, sub: 'Asist.' },
        { name: 'Toni K.', value: 9, sub: 'Asist.' }
    ]}
];
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-[110] overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <section class="absolute inset-y-0 right-0 flex max-w-full pl-10">
            <Transition
                enter-active-class="transform transition ease-in-out duration-500"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div v-if="show" class="w-screen max-w-md">
                    <div class="flex h-full flex-col bg-white shadow-2xl dark:bg-surface-dark border-l border-slate-200 dark:border-slate-800">

                        <div class="p-8 pb-0">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-black uppercase tracking-tighter text-slate-900 dark:text-white">
                                    {{ team?.name }}
                                </h2>
                                <button @click="$emit('close')" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Estadísticas de Temporada</p>
                        </div>

                        <div class="flex-1 overflow-y-auto p-8 space-y-8">
                            <div v-for="group in stats" :key="group.name">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="material-symbols-outlined text-slate-400 !text-lg">{{ group.icon }}</span>
                                    <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ group.name }}</h3>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="item in group.list" :key="item.name"
                                         class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-slate-800/50">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ item.name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-lg font-black text-primary">{{ item.value }}</span>
                                            <span class="ml-1 text-[9px] font-bold uppercase text-slate-400">{{ item.sub }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 rounded-3xl bg-slate-900 p-6 text-white dark:bg-primary/10">
                                <h4 class="text-[10px] font-black uppercase tracking-widest mb-4">Rendimiento General</h4>
                                <div class="flex items-end justify-between h-20 gap-2">
                                    <div v-for="i in 5" :key="i"
                                         class="w-full bg-primary/40 rounded-t-lg transition-all hover:bg-primary"
                                         :style="{ height: Math.floor(Math.random() * 100) + '%' }">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 border-t border-slate-100 dark:border-slate-800">
                            <button class="w-full py-4 rounded-2xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-800 dark:bg-primary dark:hover:bg-primary/80 transition-all">
                                Ver Perfil Completo
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </section>
    </div>
</template>
