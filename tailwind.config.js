const defaultTheme = require('tailwindcss/defaultTheme')
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ['class'],
    content: [
        // './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{ts,js,vue}',
        '!./resources/css/**.*.css',
        // './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            transitionDelay: {
                '9000': '9000ms',
            },
            gridTemplateColumns: ({theme}) => ({
                'page': `auto minmax(0, ${theme('screens.xl')}) auto`,
                'reservation-1': 'auto repeat(1, minmax(0, 30px)) 1fr',
                'reservation-2': 'auto repeat(2, minmax(0, 30px)) 1fr',
                'reservation-3': 'auto repeat(3, minmax(0, 30px)) 1fr',
                'reservation-4': 'auto repeat(4, minmax(0, 30px)) 1fr',
                'reservation-5': 'auto repeat(5, minmax(0, 30px)) 1fr',
                'sm-reservation-1': '2fr repeat(1, minmax(0, 30px))',
                'sm-reservation-2': '2fr repeat(2, minmax(0, 30px))',
                'sm-reservation-3': '2fr repeat(3, minmax(0, 30px))',
                'sm-reservation-4': '2fr repeat(4, minmax(0, 30px))',
                'sm-reservation-5': '2fr repeat(5, minmax(0, 30px))',
            }),
        colors: ({theme}) => ({
            primary: {
                DEFAULT: colors.violet['500'],
                hover: colors.violet['600'],
                active: colors.violet['900'],
                dark: colors.violet['300'],
            },
            secondary: {
                DEFAULT: colors.slate['500'],
                hover: colors.slate['600'],
                active: colors.slate['900'],
                dark: colors.slate['300'],
            },
            success: {
                DEFAULT: colors.green['500'],
                hover: colors.green['600'],
                active: colors.green['900'],
                dark: colors.green['300'],
            },
            info: {
                DEFAULT: colors.blue['500'],
                hover: colors.blue['600'],
                active: colors.blue['900'],
                dark: colors.blue['300'],
            },
            danger: {
                DEFAULT: colors.amber['500'],
                hover: colors.amber['600'],
                active: colors.amber['900'],
                dark: colors.amber['300'],
            },
            help: {
                DEFAULT: colors.purple['500'],
                hover: colors.purple['600'],
                active: colors.purple['900'],
                dark: colors.purple['300'],
            },
            warning: {
                DEFAULT: colors.red['500'],
                hover: colors.red['600'],
                active: colors.red['900'],
                dark: colors.red['300'],
            },
        }),
        },

    },

    safelist: [
        'bg-transparent',
        'border',
        'border-1',
        'border-2',
        'hover:text-white',
        'active:text-white',
        // {
        //     pattern: /(bg|text)-([^/]+violet|slate|green|blue|amber|purple|red)-(300|500|600|900)+/,
        //     variants: ['hover', 'focus', 'dark:hover', 'dark:focus'],
        // },
    ],

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('flowbite/plugin'),
        require('@vueform/slider/tailwind'),
        require('tailwindcss-primeui'),
    ],
}
