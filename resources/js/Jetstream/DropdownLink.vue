<script setup>
import {Link} from '@inertiajs/vue3';
import {computed} from "vue";

const props = defineProps({
    href: String,
    as: String,
    isMenu: Boolean,
    innerClasses: String,
});

const classes = computed(() => {
    let classes = props.innerClasses;
    if (props.isMenu) {
        classes += ' hover:no-underline';
    }
    return classes;
});
</script>

<template>
    <div>
        <button v-if="as === 'button'"
                type="submit"
                :class="classes"
                class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 focus:bg-gray-800 dark:text-gray-100 hover:dark:text-gray-300 transition">
            <slot/>
        </button>

        <a v-else-if="as ==='a'"
           :href="href"
           :class="classes"
           class="no-underline block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 focus:bg-gray-800 dark:text-gray-100 hover:dark:text-gray-300 transition">
            <slot/>
        </a>

        <Link v-else
              :href="href"
              :class="classes"
              class="no-underline block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 focus:bg-gray-800 dark:text-gray-100 hover:dark:text-gray-300 transition">
            <slot/>
        </Link>
    </div>
</template>
