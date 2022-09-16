<script setup>
    import { Link } from '@inertiajs/inertia-vue3'
    import { computed } from 'vue'

    const props = defineProps({
        href: String,
        active: Boolean,
    })

    const classes = computed(() => {
        return props.active
            ? 'no-underline inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition'
            : 'no-underline inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition'
    })
</script>

<template>
    <Link :href="href" :class="classes">
        <slot/>
    </Link>
</template>
