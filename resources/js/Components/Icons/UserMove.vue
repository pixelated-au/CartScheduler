<script setup>
    defineProps({
        color: {
            type: String,
            default: '#000000',
        },
    })
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" :fill="color">
        <path
            d="M14 14.252V22H4a8 8 0 0 1 10-7.748ZM12 13c-3.315 0-6-2.685-6-6s2.685-6 6-6 6 2.685 6 6-2.685 6-6 6Zm6.586 4-1.829-1.828 1.415-1.415L22.414 18l-4.242 4.243-1.415-1.415L18.586 19H15v-2h3.586Z"/>
    </svg>
</template>
