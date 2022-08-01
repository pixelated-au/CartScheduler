const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: [
        'bg-transparent',
        'border',
        'border-1',
        'border-2',
        'hover:text-white',
        'active:text-white',
        {
            pattern: /(violet|slate|green|blue|amber|purple|red)-(300|500|600|900)/,
            variants: ['hover', 'focus'],
        },
    ],

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
}
