import {usePage} from "@inertiajs/inertia-vue3";
import {createGlobalState, useStorage} from "@vueuse/core";


export const useGlobalState = createGlobalState(
    () =>
        useStorage('cart-scheduler-store', {})
)
