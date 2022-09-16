<script setup>
    import BaseButton from '@/Components/EditorToolbar/BaseButton.vue'
    import { defineProps } from 'vue'

    defineProps({
        direction: {
            type: String,
            required: true,
        },
    })
</script>

<template>
    <BaseButton action="setTextAlign" :action-props="direction" :tooltip="`Text ${direction}`">
        <svg v-if="direction === 'left'"
             xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             width="16"
             height="16"
             class="dark:fill-gray-100">
            <path fill="none" d="M0 0h24v24H0z"/>
            <path d="M3 4h18v2H3V4zm0 15h14v2H3v-2zm0-5h18v2H3v-2zm0-5h14v2H3V9z"/>
        </svg>
        <svg v-else-if="direction === 'right'"
             xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             width="16"
             height="16"
             class="dark:fill-gray-100">
            <path fill="none" d="M0 0h24v24H0z"/>
            <path d="M3 4h18v2H3V4zm4 15h14v2H7v-2zm-4-5h18v2H3v-2zm4-5h14v2H7V9z"/>
        </svg>
        <svg v-else-if="direction === 'center'"
             xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             width="16"
             height="16"
             class="dark:fill-gray-100">
            <path fill="none" d="M0 0h24v24H0z"/>
            <path d="M3 4h18v2H3V4zm2 15h14v2H5v-2zm-2-5h18v2H3v-2zm2-5h14v2H5V9z"/>
        </svg>
        <svg v-else-if="direction === 'justify'"
             xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             width="16"
             height="16"
             class="dark:fill-gray-100">
            <path fill="none" d="M0 0h24v24H0z"/>
            <path d="M3 4h18v2H3V4zm2 15h14v2H5v-2zm-2-5h18v2H3v-2zm2-5h14v2H5V9z"/>
        </svg>
    </BaseButton>
</template>
