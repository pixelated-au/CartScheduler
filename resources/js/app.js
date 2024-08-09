import Bugsnag from '@bugsnag/js';
import BugsnagPluginVue from '@bugsnag/plugin-vue';
import {createInertiaApp} from '@inertiajs/vue3';

import 'flowbite';

import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {createApp, h} from 'vue';

import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import {ZiggyVue} from 'ziggy-js';
import '../css/app.css';
import './bootstrap';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

const bugsnagKey = import.meta.env.VITE_BUGSNAG_FRONT_END_API_KEY;

if (bugsnagKey) {
    Bugsnag.start({
        apiKey: bugsnagKey, plugins: [new BugsnagPluginVue()],
    });
}

/**
 * @function createInertiaApp(CreateInertiaAppProps): Promise<{
 *     head: string[];
 *     body: string;
 * }>
 */
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    progress: {
        color: '#4B5563',
    },
    setup({el, App, props, plugin}) {
        const vueApp = createApp({render: () => h(App, props)})
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast);

        if (bugsnagKey) {
            vueApp.use(Bugsnag.getPlugin('vue'));
        }
        vueApp.mount(el);
        return vueApp;
    },
});
