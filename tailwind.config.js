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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // "Executive Edition" brand tokens: deep navy for
                // navigation/identity, white for the working environment,
                // champagne gold for highlights and primary actions -
                // used sparingly, never as a dominant surface color.
                paper: '#F4F6F9',
                line: '#E7EAF0',
                navy: {
                    50: '#EEF1F6',
                    100: '#D7DEEA',
                    200: '#AEBDD4',
                    300: '#7E93B7',
                    400: '#4F6997',
                    500: '#2C4876',
                    600: '#18305C',
                    700: '#0F2347',
                    800: '#0B1E3D',
                    900: '#071530',
                    DEFAULT: '#0B1E3D',
                },
                gold: {
                    50: '#FBF6EC',
                    100: '#F5E9CF',
                    200: '#ECD6A3',
                    300: '#DFBD72',
                    400: '#D3A95A',
                    500: '#C9A66B',
                    600: '#A8762F',
                    700: '#845C24',
                    800: '#5F4219',
                    900: '#3D2A0F',
                },
            },
        },
    },

    plugins: [forms],
};
