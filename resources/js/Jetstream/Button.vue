<script setup>
    import useTheme from '@/Composables/useTheme'
    import { computed } from 'vue'

    const { getBackgroundColours, getBackgroundHoverColours, getOutlineColours, getOutlineHoverColours } = useTheme()

    const props = defineProps({
        type: {
            type: String,
            default: 'submit',
        },
        styleType: {
            type: String,
            default: 'primary',
        },
        outline: {
            type: Boolean,
            default: false,
        },
    })

    const cssClass = computed(() => {
        const css = []

        if (props.outline) {
            css.push(getOutlineColours(props.styleType))
            css.push(getOutlineHoverColours(props.styleType))
        } else {
            css.push(getBackgroundColours(props.styleType))
            css.push(getBackgroundHoverColours(props.styleType))
        }

        return css
    })

</script>

<template>
    <button :type="type"
            :class="cssClass"
            class="inline-flex items-center px-4 py-2 border rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
        <slot/>
    </button>
</template>
