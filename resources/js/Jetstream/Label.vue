<script setup>
    defineProps({
        value: String,
        isDisabled: {
            type: Boolean,
            default: false,
        },
    })
</script>

<template>
    <label class="block font-medium" :class="{'text-gray-800': !isDisabled, 'text-gray-400': isDisabled}">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot/></span>
    </label>
</template>
