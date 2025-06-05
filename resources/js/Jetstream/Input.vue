<script setup>
import { onMounted, ref } from "vue";

defineProps({
  modelValue: {
    type: [String, Number],
    default: "",
  },
});

defineEmits(["update:modelValue"]);

const input = ref();

onMounted(() => {
  if (input.value.hasAttribute("autofocus")) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <input
      ref="input"
      class="border-gray-300 focus:border-indigo-300 focus:ring-1 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-800  dark:focus:ring-indigo-800 dark:text-gray-100 rounded-md shadow-sm bg-transparent dark:bg-transparent"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)">
</template>
