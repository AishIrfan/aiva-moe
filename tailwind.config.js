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
                sans: ['Geist', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"Geist Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                surface: {
                    DEFAULT: '#ffffff',
                    sunken: '#fafaf9',
                    raised: '#ffffff',
                },
            },
            boxShadow: {
                'card': '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 1px 0 rgb(15 23 42 / 0.03)',
                'pop': '0 12px 32px -12px rgb(15 23 42 / 0.18), 0 4px 8px -4px rgb(15 23 42 / 0.06)',
                'inset-hairline': 'inset 0 0 0 1px rgb(228 228 231 / 0.8)',
            },
            letterSpacing: {
                'tightest': '-0.04em',
            },
        },
    },

    plugins: [forms],
};
