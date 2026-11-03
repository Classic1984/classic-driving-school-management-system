import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Refined black/gold brand tokens - a deeper, warmer
                // near-black than pure #000 (less flat/harsh), and a
                // richer, more "gold leaf" amber than stock Tailwind
                // amber (less neon). Same light-page/black-accent
                // structure as before, just a more polished palette.
                ink: {
                    50: '#F5F5F4',
                    100: '#E7E6E4',
                    200: '#C7C4C0',
                    300: '#9B9690',
                    400: '#6B655D',
                    500: '#403B34',
                    600: '#2B2722',
                    700: '#1F1C18',
                    800: '#17140F',
                    900: '#121110',
                    DEFAULT: '#121110',
                },
                gold: {
                    50: '#FBF3E1',
                    100: '#F5E3B8',
                    200: '#EACB84',
                    300: '#DEAE57',
                    400: '#D4993E',
                    500: '#C98A2E',
                    600: '#A86F20',
                    700: '#845618',
                    800: '#603E11',
                    900: '#3D280A',
                },
            },
        },
    },

    plugins: [forms],
};
