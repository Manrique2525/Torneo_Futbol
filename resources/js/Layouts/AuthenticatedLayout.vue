<script setup>
import { ref, computed } from 'vue'
import { useDarkMode } from '@/Composables/useDarkMode'
import { Link, usePage } from '@inertiajs/vue3'

import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

const { isDark, toggleDark } = useDarkMode()
const isCollapsed = ref(false)
const page = usePage()

const openMenus = ref({
    torneos: false,
    partidos: false,
    administracion: false,
})

const toggleMenu = (menu) => {
    if (isCollapsed.value) isCollapsed.value = false
    openMenus.value[menu] = !openMenus.value[menu]
}

const isRouteActive = (routeName) => {
    return route().current(routeName);
}
</script>

<template>
    <div class="relative flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-[#0F1A16]">

        <aside
            :class="[isCollapsed ? 'w-20' : 'w-72']"
            class="flex flex-col bg-white dark:bg-[#1A2C26] border-r border-slate-200 dark:border-slate-800 transition-[width] duration-300 ease-in-out shrink-0 z-50 overflow-hidden"
        >
            <div class="flex h-20 items-center gap-3 px-6 border-b border-slate-100 dark:border-slate-800/50 shrink-0">
                <div class="bg-primary flex items-center justify-center rounded-xl h-10 w-10 shrink-0 shadow-lg shadow-primary/20 text-white">
                    <span class="material-symbols-outlined text-2xl">sports_soccer</span>
                </div>

                <div :class="[isCollapsed ? 'opacity-0 -translate-x-4 pointer-events-none' : 'opacity-100 translate-x-0']"
                     class="flex flex-col transition-all duration-300 whitespace-nowrap">
                    <h1 class="text-slate-900 dark:text-white text-sm font-black uppercase tracking-widest leading-none">
                        Soccer
                    </h1>
                    <span class="text-primary text-[10px] font-bold uppercase tracking-[0.2em]">League Manager</span>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar overflow-x-hidden">

                <Link :href="route('dashboard')"
                    :class="[isRouteActive('dashboard')
                        ? 'bg-primary text-white shadow-md shadow-primary/20'
                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5']"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group">
                    <span class="material-symbols-outlined shrink-0 transition-transform group-hover:scale-110">dashboard</span>
                    <span :class="[isCollapsed ? 'opacity-0 translate-x-4' : 'opacity-100 translate-x-0']"
                          class="text-sm font-semibold whitespace-nowrap transition-all duration-300">
                        Dashboard
                    </span>
                </Link>

                <Link :href="route('users.index')"
                    :class="[isRouteActive('users.*')
                        ? 'bg-primary/10 text-primary border border-primary/20 shadow-sm shadow-primary/5'
                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5']"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group">
                    <div class="relative">
                        <span class="material-symbols-outlined shrink-0 group-hover:rotate-12 transition-transform">group</span>
                        <span v-if="isRouteActive('users.*')" class="absolute -top-1 -right-1 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                        </span>
                    </div>
                    <span :class="[isCollapsed ? 'opacity-0 translate-x-4' : 'opacity-100 translate-x-0']"
                          class="text-sm font-semibold whitespace-nowrap transition-all duration-300">
                        Usuarios
                    </span>
                </Link>

                <div class="pt-4 pb-2">
                    <p :class="[isCollapsed ? 'opacity-0' : 'opacity-100']"
                       class="px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-opacity duration-300 whitespace-nowrap">
                        Competencia
                    </p>
                    <div v-show="isCollapsed" class="border-t border-slate-100 dark:border-slate-800 mx-2 mt-2"></div>
                </div>

                <div class="space-y-1">
                    <button @click="toggleMenu('torneos')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined shrink-0">emoji_events</span>
                            <span :class="[isCollapsed ? 'opacity-0 translate-x-4' : 'opacity-100 translate-x-0']"
                                  class="text-sm font-semibold whitespace-nowrap transition-all duration-300">
                                Torneos
                            </span>
                        </div>
                        <span :class="[isCollapsed ? 'opacity-0' : 'opacity-100', { 'rotate-180': openMenus.torneos }]"
                            class="material-symbols-outlined text-sm transition-all duration-300">
                            expand_more
                        </span>
                    </button>

                    <transition
                        enter-active-class="transition-all duration-300 ease-out"
                        leave-active-class="transition-all duration-200 ease-in"
                        enter-from-class="opacity-0 -translate-y-2 max-h-0"
                        enter-to-class="opacity-100 translate-y-0 max-h-40"
                    >
                        <div v-show="openMenus.torneos && !isCollapsed" class="ml-9 space-y-1 overflow-hidden">
                            <Link href="#" class="block py-2 text-xs font-medium text-slate-500 hover:text-primary transition-colors whitespace-nowrap">
                                Gestión de Ligas
                            </Link>
                            <Link href="#" class="block py-2 text-xs font-medium text-slate-500 hover:text-primary transition-colors whitespace-nowrap">
                                Categorías
                            </Link>
                        </div>
                    </transition>
                </div>

                <Link v-for="(item, index) in [
                    { n: 'Posiciones', i: 'leaderboard', r: '#' },
                    { n: 'Partidos', i: 'sports_score', r: '#' },
                    { n: 'Horarios', i: 'calendar_month', r: '#' },
                    { n: 'Jugadores', i: 'person_search', r: '#' },
                ]" :key="index" :href="item.r"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 transition-all group">
                    <span class="material-symbols-outlined shrink-0 group-hover:text-primary transition-colors">{{ item.i }}</span>
                    <span :class="[isCollapsed ? 'opacity-0 translate-x-4' : 'opacity-100 translate-x-0']"
                          class="text-sm font-semibold whitespace-nowrap transition-all duration-300">
                        {{ item.n }}
                    </span>
                </Link>
            </nav>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-black/10 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-primary text-white flex items-center justify-center font-bold shadow-lg shadow-primary/20 shrink-0">
                        {{ $page.props.auth.user.name.substring(0, 1) }}
                    </div>
                    <div :class="[isCollapsed ? 'opacity-0 translate-x-4' : 'opacity-100 translate-x-0']"
                         class="flex flex-col overflow-hidden transition-all duration-300 whitespace-nowrap">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-[10px] text-primary font-bold uppercase tracking-tighter">Plan Premium</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="flex h-20 items-center justify-between px-8 bg-white/80 dark:bg-[#1A2C26]/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 z-40">
                <div class="flex items-center gap-4">
                    <button @click="isCollapsed = !isCollapsed"
                        class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 transition-all active:scale-95">
                        <span class="material-symbols-outlined">
                            {{ isCollapsed ? 'menu_open' : 'menu' }}
                        </span>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest transition-all">
                        <span>App</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                        <span class="text-slate-900 dark:text-white transition-colors">{{ $page.component }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button @click="toggleDark"
                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-primary transition-all active:rotate-12">
                        <span class="material-symbols-outlined text-xl">
                            {{ isDark ? 'light_mode' : 'dark_mode' }}
                        </span>
                    </button>

                    <div class="h-8 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-3 focus:outline-none group">
                                <div class="hidden md:flex flex-col items-end transition-all">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter">Administrador</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-hover:rotate-180 transition-transform duration-300">expand_more</span>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Mi Perfil </DropdownLink>
                            <div class="border-t border-slate-100 dark:border-slate-800"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-500">
                                Cerrar Sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
                <div class="max-w-[1400px] mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    @apply bg-slate-200 dark:bg-slate-800 rounded-full;
}
/* Evita que el sidebar genere scroll horizontal interno durante la animación */
aside {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
aside::-webkit-scrollbar {
    display: none;
}
</style>
