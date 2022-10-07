import Bugsnag from '@bugsnag/js'
import BugsnagPluginVue from '@bugsnag/plugin-vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'

import 'flowbite'

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m'
import '../css/app.css'
import './bootstrap'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'

const bugsnagKey = import.meta.env.VITE_BUGSNAG_FRONT_END_API_KEY

if (bugsnagKey) {
    Bugsnag.start({
        apiKey: bugsnagKey, plugins: [new BugsnagPluginVue()],
    })
}

// noinspection JSIgnoredPromiseFromCall
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup ({ el, app, props, plugin }) {
        const vueApp = createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast)

        if (bugsnagKey) {
            vueApp.use(Bugsnag.getPlugin('vue'))
        }
        vueApp.mount(el)
        return vueApp
    },
})

InertiaProgress.init({ color: '#4B5563' })
