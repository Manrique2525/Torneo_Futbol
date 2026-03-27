<template>
    <v-select v-bind="$attrs" class="vs-custom" />
</template>

<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
</script>

<style>
.vs-custom .vs__dropdown-toggle {
    background: rgb(248 250 252);
    border: 1px solid transparent;
    border-radius: 0.75rem;
    min-height: 46px;
    padding: 4px 8px;
    transition: all 0.15s;
    box-shadow: none;
}
.dark .vs-custom .vs__dropdown-toggle {
    background: rgba(255, 255, 255, 0.05);
}
.vs-custom.vs--open .vs__dropdown-toggle {
    border-color: var(--color-primary, #10b981);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary, #10b981) 20%, transparent);
}
.vs-custom .vs__selected {
    font-size: 0.875rem;
    font-weight: 600;
    color: rgb(51 65 85);
    margin: 0;
    padding: 0 4px;
    border: none;
    background: transparent;
}
.dark .vs-custom .vs__selected {
    color: rgb(226 232 240);
}
.vs-custom .vs__search,
.vs-custom .vs__search:focus {
    font-size: 0.875rem;
    color: rgb(51 65 85);
    margin: 0;
    padding: 0 4px;
    border: none;
    outline: none;
    box-shadow: none;
}
.dark .vs-custom .vs__search {
    color: rgb(226 232 240);
}
.vs-custom .vs__search::placeholder {
    color: rgb(203 213 225);
}
.dark .vs-custom .vs__search::placeholder {
    color: rgb(71 85 105);
}
.vs-custom .vs__open-indicator,
.vs-custom .vs__clear {
    color: rgb(148 163 184);
}
.vs__dropdown-menu {
    background: white;
    border: 1px solid rgb(241 245 249);
    border-radius: 0.75rem !important;
    box-shadow: 0 20px 40px -8px rgba(0,0,0,0.12);
    padding: 4px;
    max-height: 220px !important;
    overflow-y: auto !important;
    z-index: 9999 !important;
}
.dark .vs__dropdown-menu {
    background: #1A2C26;
    border-color: rgb(51 65 85);
}
.vs__dropdown-option {
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(71 85 105);
    border-radius: 0.5rem;
    padding: 8px 12px;
    cursor: pointer;
}
.dark .vs__dropdown-option {
    color: rgb(203 213 225);
}
.vs__dropdown-option--highlight {
    background: color-mix(in srgb, var(--color-primary, #10b981) 12%, transparent) !important;
    color: var(--color-primary, #10b981) !important;
}
.vs__dropdown-option--selected {
    background: var(--color-primary, #10b981) !important;
    color: white !important;
    font-weight: 700;
}
</style>