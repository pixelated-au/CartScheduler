<script setup lang="ts">
defineProps({
  action: {
    type: String,
    required: false,
    default: "edit",
    validator: (value) => ["edit", "add"].includes(value),
  },
  isDirty: Boolean,
  success: Boolean,
  processing: Boolean,
});
</script>

<template>
  <PButton v-bind="$attrs"
           severity="secondary"
           variant="outlined"
           icon="iconify mdi--arrow-left-circle-outline"
           :label="isDirty ? 'Cancel' : 'Back'"
           :disabled="processing" />
</template>
