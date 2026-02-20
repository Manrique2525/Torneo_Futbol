import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                "primary": "#10b77f",
                "primary-dark": "#0d9165", // Faltaba este
                "background-light": "#f6f8f7",
                "background-dark": "#10221c",
                "surface-light": "#ffffff", // Faltaba este
                "surface-dark": "#1a2c26",  // Faltaba este
                success: '#16A34A',
                warning: '#F59E0B',
                danger: '#DC2626',
            },
            fontFamily: {
                // El ejemplo original usa 'display' para la fuente Inter,
                // pero si la pones en 'sans' se aplicará por defecto a todo, lo cual es válido.
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
