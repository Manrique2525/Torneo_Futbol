<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});

const isMenuOpen = ref(false);
const activeTab = ref('anual');
const isDark = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

function toggleTheme() {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}

const planes = {
    mensual: [
        {
            nombre: 'Básico',
            precio: 299,
            descripcion: 'Ideal para ligas pequeñas que inician',
            popular: false,
            features: [
                '2 torneos activos',
                'Hasta 20 equipos',
                'Hasta 300 jugadores',
                '2 usuarios admin',
                'Estadísticas básicas',
                'Soporte por email',
            ],
            limitaciones: ['Sin app móvil', 'Sin streaming'],
        },
        {
            nombre: 'Pro',
            precio: 799,
            descripcion: 'Para ligas en crecimiento que quieren más',
            popular: true,
            features: [
                '10 torneos activos',
                'Hasta 60 equipos',
                'Hasta 1,500 jugadores',
                '10 usuarios admin',
                'Estadísticas avanzadas',
                'App móvil incluida',
                'Notificaciones WhatsApp',
                'Soporte prioritario',
            ],
            limitaciones: [],
        },
        {
            nombre: 'Empresarial',
            precio: 1999,
            descripcion: 'Control total para organizaciones grandes',
            popular: false,
            features: [
                'Torneos ilimitados',
                'Equipos ilimitados',
                'Jugadores ilimitados',
                'Usuarios ilimitados',
                'API completa',
                'Streaming integrado',
                'Dominio personalizado',
                'Soporte dedicado 24/7',
            ],
            limitaciones: [],
        },
    ],
    anual: [
        {
            nombre: 'Básico',
            precio: 249,
            precioOriginal: 299,
            descripcion: 'Ideal para ligas pequeñas que inician',
            popular: false,
            features: [
                '2 torneos activos',
                'Hasta 20 equipos',
                'Hasta 300 jugadores',
                '2 usuarios admin',
                'Estadísticas básicas',
                'Soporte por email',
            ],
            limitaciones: ['Sin app móvil', 'Sin streaming'],
        },
        {
            nombre: 'Pro',
            precio: 649,
            precioOriginal: 799,
            descripcion: 'Para ligas en crecimiento que quieren más',
            popular: true,
            features: [
                '10 torneos activos',
                'Hasta 60 equipos',
                'Hasta 1,500 jugadores',
                '10 usuarios admin',
                'Estadísticas avanzadas',
                'App móvil incluida',
                'Notificaciones WhatsApp',
                'Soporte prioritario',
            ],
            limitaciones: [],
        },
        {
            nombre: 'Empresarial',
            precio: 1699,
            precioOriginal: 1999,
            descripcion: 'Control total para organizaciones grandes',
            popular: false,
            features: [
                'Torneos ilimitados',
                'Equipos ilimitados',
                'Jugadores ilimitados',
                'Usuarios ilimitados',
                'API completa',
                'Streaming integrado',
                'Dominio personalizado',
                'Soporte dedicado 24/7',
            ],
            limitaciones: [],
        },
    ],
};

const features = [
    {
        icon: 'trophy',
        title: 'Gestión de Torneos',
        desc: 'Crea torneos con múltiples formatos: liga, copa, eliminación directa o fase de grupos. Configúralo todo en minutos.',
    },
    {
        icon: 'users',
        title: 'Equipos y Jugadores',
        desc: 'Administra inscripciones, transferencias, documentación y credenciales de cada jugador desde un solo lugar.',
    },
    {
        icon: 'calendar',
        title: 'Fixture Automático',
        desc: 'Genera jornadas y calendarios automáticamente. Asigna canchas, horarios y árbitros sin esfuerzo.',
    },
    {
        icon: 'bar-chart',
        title: 'Estadísticas en Vivo',
        desc: 'Goles, tarjetas, asistencias, tabla de posiciones y tabla de goleo actualizados en tiempo real.',
    },
    {
        icon: 'shield',
        title: 'Control Disciplinario',
        desc: 'Seguimiento automático de tarjetas, suspensiones, multas y apelaciones con reglas configurables.',
    },
    {
        icon: 'dollar',
        title: 'Pagos y Finanzas',
        desc: 'Controla inscripciones, multas, pagos a árbitros y genera reportes financieros de tu liga.',
    },
];

const stats = [
    { number: '2,500+', label: 'Torneos gestionados' },
    { number: '15,000+', label: 'Equipos registrados' },
    { number: '180,000+', label: 'Jugadores activos' },
    { number: '99.9%', label: 'Uptime garantizado' },
];
</script>

<template>
    <Head title="Gestión de Torneos - Plataforma SaaS" />

    <!-- Google Fonts -->
    <component is="teleport" to="head">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap"
            rel="stylesheet"
        />
    </component>

    <div
        class="bg-background-light dark:bg-background-dark text-gray-800 dark:text-white overflow-x-hidden transition-colors duration-500"
        style="font-family: 'Plus Jakarta Sans', sans-serif"
    >
        <!-- ═══════════════ NAVBAR ═══════════════ -->
        <nav class="fixed top-0 left-0 right-0 z-50 border-b border-gray-200/60 dark:border-white/5 backdrop-blur-xl bg-white/80 dark:bg-background-dark/80 transition-colors duration-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-20">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l-8 8m0-8l8 8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight" style="font-family: 'Outfit', sans-serif">
                            <span class="text-gray-900 dark:text-white transition-colors duration-500">Torneo</span><span class="text-primary">Hub</span>
                        </span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden lg:flex items-center gap-8">
                        <a href="#features" class="text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white transition-colors duration-200">Funciones</a>
                        <a href="#pricing" class="text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white transition-colors duration-200">Precios</a>
                        <a href="#stats" class="text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white transition-colors duration-200">Resultados</a>
                    </div>

                    <!-- Auth + Theme Toggle -->
                    <div class="flex items-center gap-3">
                        <!-- Theme Toggle -->
                        <button
                            @click="toggleTheme"
                            class="relative w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 dark:border-white/10 bg-white dark:bg-surface-dark text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white hover:border-gray-300 dark:hover:border-white/20 transition-all duration-300 hover:shadow-md"
                            :aria-label="isDark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
                        >
                            <!-- Sun -->
                            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <!-- Moon -->
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </button>

                        <!-- Desktop Auth -->
                        <div v-if="canLogin" class="hidden lg:flex items-center gap-3">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-5 py-2.5 text-sm font-semibold bg-primary hover:bg-primary-dark text-white rounded-xl transition-all duration-200"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="px-5 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-white/70 dark:hover:text-white transition-colors duration-200"
                                >
                                    Iniciar sesión
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="px-5 py-2.5 text-sm font-semibold bg-primary hover:bg-primary-dark text-white rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-primary/25"
                                >
                                    Prueba gratis
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button @click="isMenuOpen = !isMenuOpen" class="lg:hidden p-2 text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <svg v-if="!isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="isMenuOpen" class="lg:hidden border-t border-gray-200/60 dark:border-white/5 bg-white/95 dark:bg-background-dark/95 backdrop-blur-xl px-4 py-6 space-y-4 transition-colors duration-500">
                    <a href="#features" class="block text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white" @click="isMenuOpen = false">Funciones</a>
                    <a href="#pricing" class="block text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white" @click="isMenuOpen = false">Precios</a>
                    <a href="#stats" class="block text-sm text-gray-500 hover:text-gray-900 dark:text-white/60 dark:hover:text-white" @click="isMenuOpen = false">Resultados</a>
                    <div v-if="canLogin" class="pt-4 border-t border-gray-200 dark:border-white/10 space-y-3">
                        <template v-if="!$page.props.auth.user">
                            <Link :href="route('login')" class="block text-sm text-gray-500 dark:text-white/70">Iniciar sesión</Link>
                            <Link v-if="canRegister" :href="route('register')" class="block w-full text-center px-5 py-2.5 text-sm font-semibold bg-primary text-white rounded-xl">
                                Prueba gratis
                            </Link>
                        </template>
                        <Link v-else :href="route('dashboard')" class="block w-full text-center px-5 py-2.5 text-sm font-semibold bg-primary text-white rounded-xl">
                            Dashboard
                        </Link>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- ═══════════════ HERO ═══════════════ -->
        <section class="relative min-h-screen flex items-center pt-20">
            <!-- Background Effects -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-full bg-gradient-to-b from-primary/[0.04] via-transparent to-transparent dark:from-transparent transition-colors duration-500"></div>
                <div class="absolute top-1/4 -left-32 w-[500px] h-[500px] bg-primary/[0.06] dark:bg-primary/10 rounded-full blur-[120px] transition-colors duration-500"></div>
                <div class="absolute bottom-1/4 -right-32 w-[400px] h-[400px] bg-emerald-300/[0.06] dark:bg-primary/5 rounded-full blur-[100px] transition-colors duration-500"></div>
                <!-- Grid pattern -->
                <div
                    class="absolute inset-0 opacity-[0.04] dark:opacity-[0.03]"
                    :style="{
                        backgroundImage: isDark
                            ? 'linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px)'
                            : 'linear-gradient(rgba(0,0,0,.06) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,.06) 1px, transparent 1px)',
                        backgroundSize: '60px 60px'
                    }"
                ></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-8 animate-fade-in">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        <span class="text-xs font-semibold text-primary tracking-wide uppercase">Plataforma #1 en gestión de torneos</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6 text-gray-900 dark:text-white transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                        Tu liga,
                        <span class="relative inline-block">
                            <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-primary via-emerald-500 to-primary">organizada</span>
                            <span class="absolute bottom-2 left-0 right-0 h-3 bg-primary/15 dark:bg-primary/20 -skew-x-3 rounded transition-colors duration-500"></span>
                        </span>
                        <br />como profesional
                    </h1>

                    <p class="text-lg sm:text-xl text-gray-500 dark:text-white/50 max-w-2xl mx-auto mb-10 leading-relaxed transition-colors duration-500">
                        Gestiona torneos, equipos, jugadores, estadísticas y pagos desde una sola plataforma.
                        Automatiza lo tedioso y enfócate en lo que importa: <span class="text-gray-700 dark:text-white/80 font-medium transition-colors duration-500">el fútbol.</span>
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="group relative w-full sm:w-auto px-8 py-4 bg-primary hover:bg-primary-dark text-white font-semibold rounded-2xl transition-all duration-300 hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-0.5"
                        >
                            <span class="flex items-center justify-center gap-2">
                                Comenzar gratis
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </Link>
                        <a
                            href="#features"
                            class="w-full sm:w-auto px-8 py-4 border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 dark:border-white/10 dark:hover:border-white/25 dark:text-white/70 dark:hover:text-white font-medium rounded-2xl transition-all duration-300 text-center"
                        >
                            Ver funciones
                        </a>
                    </div>

                    <!-- Trust badges -->
                    <div class="mt-16 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-xs text-gray-400 dark:text-white/30 transition-colors duration-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary/70" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            14 días gratis
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary/70" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Sin tarjeta de crédito
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary/70" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Cancela cuando quieras
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-300 dark:text-white/20 transition-colors duration-500">
                <span class="text-[10px] uppercase tracking-[0.2em]">Scroll</span>
                <div class="w-5 h-8 rounded-full border border-gray-300 dark:border-white/20 flex justify-center pt-1.5 transition-colors duration-500">
                    <div class="w-1 h-2 bg-primary/60 rounded-full animate-bounce"></div>
                </div>
            </div>
        </section>

        <!-- ═══════════════ STATS ═══════════════ -->
        <section id="stats" class="relative py-20 border-y border-gray-200/60 dark:border-white/5 bg-white/50 dark:bg-transparent transition-colors duration-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-2 transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                            {{ stat.number }}
                        </div>
                        <div class="text-sm text-gray-400 dark:text-white/40 transition-colors duration-500">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════ FEATURES ═══════════════ -->
        <section id="features" class="relative py-24 lg:py-32">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary/[0.04] dark:bg-primary/5 rounded-full blur-[150px] transition-colors duration-500"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section header -->
                <div class="max-w-2xl mx-auto text-center mb-16 lg:mb-20">
                    <span class="text-xs font-semibold text-primary tracking-[0.2em] uppercase">Funcionalidades</span>
                    <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                        Todo lo que necesitas,<br />
                        <span class="text-gray-300 dark:text-white/40 transition-colors duration-500">nada que no.</span>
                    </h2>
                </div>

                <!-- Feature Grid -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="(feature, idx) in features"
                        :key="idx"
                        class="group relative p-7 rounded-2xl border border-gray-200/80 dark:border-white/[0.06] bg-white/70 dark:bg-surface-dark/50 hover:bg-white dark:hover:bg-surface-dark hover:border-primary/20 dark:hover:border-primary/20 hover:shadow-xl hover:shadow-gray-200/50 dark:hover:shadow-none transition-all duration-500"
                    >
                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-5 group-hover:bg-primary/15 dark:group-hover:bg-primary/20 transition-colors duration-300">
                            <svg v-if="feature.icon === 'trophy'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.98 6.98 0 01-3.77 1.522m0 0a6.98 6.98 0 01-3.77-1.522" />
                            </svg>
                            <svg v-if="feature.icon === 'users'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <svg v-if="feature.icon === 'calendar'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <svg v-if="feature.icon === 'bar-chart'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            <svg v-if="feature.icon === 'shield'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <svg v-if="feature.icon === 'dollar'" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 transition-colors duration-500">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-white/40 leading-relaxed transition-colors duration-500">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════ PRICING ═══════════════ -->
        <section id="pricing" class="relative py-24 lg:py-32">
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-primary/[0.04] dark:bg-primary/5 rounded-full blur-[150px] transition-colors duration-500"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section header -->
                <div class="max-w-2xl mx-auto text-center mb-12 lg:mb-16">
                    <span class="text-xs font-semibold text-primary tracking-[0.2em] uppercase">Planes y precios</span>
                    <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                        Un plan para cada
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-emerald-400">liga</span>
                    </h2>
                    <p class="mt-4 text-gray-400 dark:text-white/40 transition-colors duration-500">Empieza gratis. Escala cuando lo necesites.</p>

                    <!-- Toggle -->
                    <div class="mt-8 inline-flex items-center p-1 rounded-2xl bg-gray-100 dark:bg-surface-dark border border-gray-200/80 dark:border-white/[0.06] transition-colors duration-500">
                        <button
                            @click="activeTab = 'mensual'"
                            :class="[
                                'px-6 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300',
                                activeTab === 'mensual'
                                    ? 'bg-primary text-white shadow-lg shadow-primary/25'
                                    : 'text-gray-400 hover:text-gray-600 dark:text-white/40 dark:hover:text-white/70'
                            ]"
                        >
                            Mensual
                        </button>
                        <button
                            @click="activeTab = 'anual'"
                            :class="[
                                'px-6 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300 flex items-center gap-2',
                                activeTab === 'anual'
                                    ? 'bg-primary text-white shadow-lg shadow-primary/25'
                                    : 'text-gray-400 hover:text-gray-600 dark:text-white/40 dark:hover:text-white/70'
                            ]"
                        >
                            Anual
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold transition-colors duration-500">-17%</span>
                        </button>
                    </div>
                </div>

                <!-- Pricing Cards -->
                <div class="grid lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <div
                        v-for="(plan, idx) in planes[activeTab]"
                        :key="plan.nombre"
                        :class="[
                            'relative rounded-3xl p-8 transition-all duration-500',
                            plan.popular
                                ? 'bg-gradient-to-b from-primary/10 via-white to-white dark:from-primary/10 dark:via-surface-dark dark:to-surface-dark border-2 border-primary/30 lg:scale-105 shadow-2xl shadow-primary/10'
                                : 'bg-white dark:bg-surface-dark/50 border border-gray-200/80 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/10 hover:shadow-lg hover:shadow-gray-200/30 dark:hover:shadow-none'
                        ]"
                    >
                        <!-- Popular Badge -->
                        <div v-if="plan.popular" class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="px-4 py-1.5 text-xs font-bold bg-primary text-white rounded-full shadow-lg shadow-primary/30 uppercase tracking-wider">
                                Más popular
                            </span>
                        </div>

                        <!-- Plan header -->
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-500">{{ plan.nombre }}</h3>
                            <p class="mt-1 text-sm text-gray-400 dark:text-white/40 transition-colors duration-500">{{ plan.descripcion }}</p>
                        </div>

                        <!-- Price -->
                        <div class="mb-8">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                                    ${{ plan.precio }}
                                </span>
                                <span class="text-gray-400 dark:text-white/30 text-sm transition-colors duration-500">/mes</span>
                            </div>
                            <div v-if="plan.precioOriginal" class="mt-1 text-sm text-gray-400 dark:text-white/30 transition-colors duration-500">
                                <span class="line-through">${{ plan.precioOriginal }}</span>
                                <span class="text-primary ml-1">Ahorra ${{ (plan.precioOriginal - plan.precio) * 12 }}/año</span>
                            </div>
                            <div class="text-xs text-gray-300 dark:text-white/20 mt-1 transition-colors duration-500">MXN + IVA</div>
                        </div>

                        <!-- CTA -->
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            :class="[
                                'block w-full text-center px-6 py-3.5 rounded-xl font-semibold text-sm transition-all duration-300 mb-8',
                                plan.popular
                                    ? 'bg-primary hover:bg-primary-dark text-white shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30'
                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/5 dark:hover:bg-white/10 dark:text-white border border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'
                            ]"
                        >
                            Comenzar con {{ plan.nombre }}
                        </Link>

                        <!-- Features -->
                        <ul class="space-y-3">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-white/60 transition-colors duration-500">{{ feature }}</span>
                            </li>
                            <li v-for="limit in plan.limitaciones" :key="limit" class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-200 dark:text-white/15 flex-shrink-0 mt-0.5 transition-colors duration-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-gray-300 dark:text-white/25 transition-colors duration-500">{{ limit }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Enterprise CTA -->
                <div class="mt-12 text-center">
                    <p class="text-sm text-gray-400 dark:text-white/30 transition-colors duration-500">
                        ¿Necesitas algo personalizado?
                        <a href="#" class="text-primary hover:text-primary-dark underline underline-offset-4 transition-colors">Contáctanos</a>
                        para un plan a tu medida.
                    </p>
                </div>
            </div>
        </section>

        <!-- ═══════════════ CTA FINAL ═══════════════ -->
        <section class="relative py-24 lg:py-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary/10 via-white to-white dark:from-primary/20 dark:via-surface-dark dark:to-surface-dark border border-primary/10 p-10 sm:p-16 lg:p-20 transition-colors duration-500">
                    <!-- Decorative -->
                    <div class="absolute top-0 right-0 w-80 h-80 bg-primary/10 rounded-full blur-[100px]"></div>
                    <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-primary/5 rounded-full blur-[80px]"></div>

                    <div class="relative max-w-2xl">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-gray-900 dark:text-white transition-colors duration-500" style="font-family: 'Outfit', sans-serif">
                            Lleva tu liga al<br />
                            <span class="text-primary">siguiente nivel</span>
                        </h2>
                        <p class="mt-5 text-lg text-gray-500 dark:text-white/40 max-w-lg transition-colors duration-500">
                            Únete a cientos de organizadores que ya gestionan sus torneos de forma profesional.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-8 py-4 bg-primary hover:bg-primary-dark text-white font-semibold rounded-2xl transition-all duration-300 hover:shadow-2xl hover:shadow-primary/30 text-center"
                            >
                                Comenzar gratis — 14 días
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════ FOOTER ═══════════════ -->
        <footer class="border-t border-gray-200/60 dark:border-white/5 py-12 lg:py-16 bg-white/30 dark:bg-transparent transition-colors duration-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">
                    <!-- Brand -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l-8 8m0-8l8 8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                                </svg>
                            </div>
                            <span class="text-lg font-bold" style="font-family: 'Outfit', sans-serif">
                                <span class="text-gray-900 dark:text-white transition-colors duration-500">Torneo</span><span class="text-primary">Hub</span>
                            </span>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-white/30 leading-relaxed transition-colors duration-500">
                            La plataforma SaaS para organizar y gestionar torneos de fútbol como un profesional.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase tracking-wider mb-4 transition-colors duration-500">Producto</h4>
                        <ul class="space-y-3">
                            <li><a href="#features" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Funciones</a></li>
                            <li><a href="#pricing" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Precios</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Integraciones</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">API</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase tracking-wider mb-4 transition-colors duration-500">Soporte</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Centro de ayuda</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Documentación</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Contacto</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase tracking-wider mb-4 transition-colors duration-500">Legal</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Términos</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Privacidad</a></li>
                            <li><a href="#" class="text-sm text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white transition-colors">Aviso legal</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200/60 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors duration-500">
                    <p class="text-xs text-gray-300 dark:text-white/20 transition-colors duration-500">&copy; {{ new Date().getFullYear() }} TorneoHub. Todos los derechos reservados.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="text-gray-300 dark:text-white/20 hover:text-gray-500 dark:hover:text-white/50 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557a9.83 9.83 0 01-2.828.775 4.932 4.932 0 002.165-2.724 9.864 9.864 0 01-3.127 1.195 4.916 4.916 0 00-8.38 4.482A13.944 13.944 0 011.671 3.149a4.916 4.916 0 001.523 6.574 4.897 4.897 0 01-2.229-.616v.062a4.918 4.918 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.937 4.937 0 004.604 3.417 9.868 9.868 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63a9.936 9.936 0 002.46-2.548l-.047-.02z"/></svg>
                        </a>
                        <a href="#" class="text-gray-300 dark:text-white/20 hover:text-gray-500 dark:hover:text-white/50 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/></svg>
                        </a>
                        <a href="#" class="text-gray-300 dark:text-white/20 hover:text-gray-500 dark:hover:text-white/50 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Animations -->
    <component is="style">
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }
    </component>
</template>
