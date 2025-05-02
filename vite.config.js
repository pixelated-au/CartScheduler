import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import {defineConfig} from 'vite'
import vueDevTools from "vite-plugin-vue-devtools";

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vueDevTools({
            appendTo: "resources/js/app.js",
            // Set the editor by setting the env var on your $PATH: export LAUNCH_EDITOR=[your editor path or executable]
            // See: https://devtools.vuejs.org/getting-started/open-in-editor
            // and https://github.com/webfansplz/vite-plugin-vue-inspector?tab=readme-ov-file#--configuration-ide--editor
        }),
    ],
    server: {
        host: '0.0.0.0',
    },
    compilerOptions: {
        baseUrl: ".",
        paths: {
            "@/*": [
                "./resources/js/*"
            ]
        }
    },
    envDir: './',
})
