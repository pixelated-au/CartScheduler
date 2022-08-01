<script setup>
    import useTheme from '@/Composables/useTheme'
    import { computed } from 'vue'

    const { getBackgroundColours, getBackgroundHoverColours } = useTheme()

    const props = defineProps({
        type: {
            type: String,
            default: 'submit',
        },
        styleType: {
            type: String,
            default: 'primary',
        },
    })

    const cssClass = computed(() => {
        return [
            getBackgroundColours(props.styleType),
            getBackgroundHoverColours(props.styleType),
        ]
    })

</script>

<template>
    <button :type="type"
            :class="cssClass"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
        <slot/>
    </button>
</template>
