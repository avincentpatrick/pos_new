import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php'
    ],

    safelist: [
        'sm:max-w-sm',
        'sm:max-w-md',
        'sm:max-w-lg',
        'sm:max-w-xl',
        'sm:max-w-2xl',
        'sm:max-w-3xl',
        'sm:max-w-4xl',
        'sm:max-w-5xl',
        'sm:max-w-6xl',
        'sm:max-w-7xl',
        'bg-blue-100', 'text-blue-800', 'text-blue-900',
        'bg-green-100', 'text-green-800', 'text-green-900',
        'bg-yellow-100', 'text-yellow-800', 'text-yellow-900',
        'bg-indigo-100', 'text-indigo-800', 'text-indigo-900',
        'bg-orange-100', 'text-orange-800', 'text-orange-900',
        'bg-red-100', 'text-red-800', 'text-red-900',
        'bg-pink-100', 'text-pink-800', 'text-pink-900',
        'bg-purple-100', 'text-purple-800', 'text-purple-900',
        'bg-gray-100', 'text-gray-800', 'text-gray-900',
    ],

    theme: {
        extend: {
            colors: {
                'custom-orange': '#FF7F00', // A vibrant orange color
                'custom-dark-blue': '#1a2035', // A dark blue color
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
