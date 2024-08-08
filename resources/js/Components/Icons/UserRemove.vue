<script setup>
defineProps({
    color: {
        type: String,
        default: '#000000',
    },
});
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" :fill="color">
        <path
            d="M14 14.252V22H4a8 8 0 0 1 10-7.748ZM12 13c-3.315 0-6-2.685-6-6s2.685-6 6-6 6 2.685 6 6-2.685 6-6 6Zm7 3.586 2.121-2.121 1.415 1.414L20.413 18l2.121 2.121-1.414 1.415L19 19.413l-2.121 2.121-1.415-1.414L17.587 18l-2.121-2.121 1.414-1.415L19 16.587Z"/>
    </svg>
</template>
