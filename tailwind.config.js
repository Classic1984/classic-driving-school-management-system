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
                // "Warm Minimal" theme tokens (Phase 1 of the styling
                // refresh) - an ivory/charcoal base with a muted amber
                // accent, replacing the old straight black/bright-amber
                // palette. New views should reach for these instead of
                // bare black/gray/amber utilities.
                ink: '#1F1B16',
                paper: '#FAF8F4',
                line: '#ECE6DA',
                accent: {
                    50: '#FDF6EC',
                    100: '#F7E9D2',
                    200: '#F0D6A8',
                    300: '#E2B877',
                    400: '#CB954B',
                    500: '#AD6A2A',
                    600: '#8F561F',
                    700: '#73440E',
                    800: '#5C370D',
                    900: '#43280A',
                },
            },
        },
    },

    plugins: [forms],
};
