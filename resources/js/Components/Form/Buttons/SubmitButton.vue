<script setup lang="ts">
defineProps({
  action: {
    type: String,
    required: false,
    default: "edit",
    validator: (value) => ["edit", "save"].includes(value),
  },
  success: Boolean,
  processing: Boolean,
});
</script>

<template>
  <PButton v-bind="$attrs"
           :icon="success ? 'inline-block iconify mdi--tick-circle-outline success-icon' : 'iconify mdi--cloud-upload-outline'"
           loading-icon="animate-spin iconify mdi--sync"
           :label="action === 'edit' ? 'Update' : 'Save'"
           :disabled="processing"
           :loading="processing" />
</template>

<!--suppress CssUnusedSymbol -->
<style scoped>
:deep(.success-icon) {
    animation-name: pop;
    animation-duration: 750ms;
    /*noinspection CssInvalidFunction*/
    animation-timing-function: linear(0, 0.618 4.6%, 1.072 9.7%, 1.358 15.3%, 1.446 18.4%, 1.497 21.7%, 1.512 23.9%, 1.514 26.2%, 1.481 31.5%, 1.421 36.4%, 1.174 53.4%, 1.108 59.5%, 1.059 65.7%, 1.028 71.9%, 1.009 78.9%, 1);
    animation-iteration-count: 1;
}

@keyframes pop {
    0%, 100% {
        scale: .1;
    }
    100% {
        scale: 1;
    }
}
</style>
