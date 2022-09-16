const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            transitionDelay: {
                '9000': '9000ms',
            },
            gridTemplateColumns: {
                'reservation-1': '2fr repeat(1, minmax(0, 30px)) 1fr',
                'reservation-2': '2fr repeat(2, minmax(0, 30px)) 1fr',
                'reservation-3': '2fr repeat(3, minmax(0, 30px)) 1fr',
                'reservation-4': '2fr repeat(4, minmax(0, 30px)) 1fr',
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
            variants: ['hover', 'focus', 'dark:hover', 'dark:focus'],
        },
    ],

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('flowbite/plugin'),
    ],
}
