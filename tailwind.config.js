import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                emerald: {
                    50: '#eef5f8',
                    100: '#d9e7ee',
                    200: '#b9d2dd',
                    300: '#8fb7c9',
                    400: '#628dae',
                    500: '#3d6b95',
                    600: '#2f5377',
                    700: '#24415a',
                    800: '#1b3145',
                    900: '#152639',
                },
                amber: {
                    50: '#fbf5ea',
                    100: '#f7e7ca',
                    200: '#e9cc94',
                    300: '#d3a65f',
                    400: '#b68132',
                    500: '#975d20',
                    600: '#7b4b1b',
                    700: '#633b16',
                    800: '#4f2f13',
                    900: '#3f2610',
                },
            },
        },
    },

    plugins: [forms],
};
