import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-blue-500',
        'hover:bg-blue-600',
        'text-blue-500',
        'hover:text-blue-700',
        'border-blue-500',
        'bg-green-500',
        'hover:bg-green-600',
        'text-green-500',
        'hover:text-green-700',
        'border-green-500',
        'bg-orange-500',
        'hover:bg-orange-600',
        'text-orange-500',
        'hover:text-orange-700',
        'border-orange-500',
        'bg-gray-500',
        'hover:bg-gray-600',
        'text-gray-500',
        'hover:text-gray-700',
        'border-gray-500',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
