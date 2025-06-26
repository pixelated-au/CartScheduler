<script setup lang="ts">
defineProps({
  action: {
    type: String,
    required: false,
    default: "edit",
    validator: (value) => ["edit", "save"].includes(value),
  },
  isDirty: Boolean,
  success: Boolean,
  processing: Boolean,
});
</script>

<template>
  <PButton v-bind="$attrs"
           severity="info"
           label="Back"
           icon="iconify mdi--arrow-left-circle-outline"
           variant="text"/>
</template>
