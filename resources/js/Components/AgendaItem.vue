<script setup>
defineProps({
    match: Object
});
</script>

<template>
    <div class="group flex items-center gap-4 rounded-2xl p-3 transition-all hover:bg-white hover:shadow-md dark:hover:bg-white/5">
        <div class="w-16 text-center">
            <span class="text-xs font-black text-slate-900 dark:text-white">{{ match.time }}</span>
            <span class="block text-[9px] font-bold uppercase text-slate-400">Hora</span>
        </div>

        <div class="flex flex-1 items-center justify-center gap-3">
            <div class="flex flex-1 items-center justify-end gap-2 text-right">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ match.home_team }}</span>
                <div class="h-8 w-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-primary">
                    {{ match.home_team.substring(0,2) }}
                </div>
            </div>

            <div class="text-[10px] font-black text-slate-300">VS</div>

            <div class="flex flex-1 items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-primary">
                    {{ match.away_team.substring(0,2) }}
                </div>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ match.away_team }}</span>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 text-slate-400">
            <span class="material-symbols-outlined !text-sm">location_on</span>
            <span class="text-[10px] font-bold uppercase tracking-tighter">{{ match.field }}</span>
        </div>

        <button class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-primary hover:text-white transition-all">
            <span class="material-symbols-outlined !text-lg">edit_calendar</span>
        </button>
    </div>
</template>
