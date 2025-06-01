<script setup>
defineProps({
    value: String,
    isDisabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
  <label class="block font-medium"
         :class="{'text-gray-800 dark:text-gray-200': !isDisabled, 'text-gray-400 dark:text-gray-600': isDisabled}">
    <span v-if="value">{{ value }}</span>
    <span v-else><slot/></span>
  </label>
</template>
