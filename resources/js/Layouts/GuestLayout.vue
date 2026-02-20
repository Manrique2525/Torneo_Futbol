<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12 transition-all duration-500
               bg-gradient-to-b from-primary/30 via-slate-100 to-slate-300
               dark:from-[#1a3d32] dark:via-[#10221c] dark:to-[#050a09]">

        <div class="w-full max-w-md overflow-hidden bg-white/90 dark:bg-[#1A2C26]/90 backdrop-blur-xl p-6 shadow-2xl rounded-3xl md:p-8 border border-slate-200 dark:border-white/10 ring-1 ring-black/5 relative z-10">

            <div class="mb-8 flex flex-col items-center justify-center">
                <Link href="/" class="flex flex-col items-center group">
                    <ApplicationLogo
                        class="h-20 w-auto fill-current text-primary transition-transform group-hover:scale-105 duration-300" />

                    <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white uppercase">
                        Soccer <span class="text-primary">League</span>
                    </h1>
                    <div class="h-1.5 w-12 bg-primary rounded-full mt-1"></div>
                </Link>
            </div>

            <slot />
        </div>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">
            &copy; 2026 Sistema de Gestión de Torneos
        </p>
    </div>
</template>
