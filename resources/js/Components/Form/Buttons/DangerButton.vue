<script setup>
defineProps({
  action: {
    type: String,
    required: false,
    default: "edit",
    validator: (value) => ["edit", "add"].includes(value),
  },
  processing: Boolean,
});
</script>

<template>
  <PButton v-bind="$attrs"
           severity="danger"
           variant="outlined"
           icon="iconify mdi--warning-circle-outline"
           :disabled="processing"/>
</template>
