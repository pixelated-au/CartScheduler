<script setup>
import { computed } from "vue";
import LabelError from "@/Jetstream/LabelError.vue";

const props = defineProps({
  value: String,
  isDisabled: {
    type: Boolean,
    default: false,
  },
  errorKey: {
    type: String,
    default: undefined,
  },
  form: {
    type: Object,
    default: undefined,
    validator: (value, props) => typeof value === "object"
      && !Array.isArray(value)
      && value !== null
      && props.errorKey,
  },
});

const invalid = computed(() => !!props.form?.invalid(props.errorKey));
</script>

<template>
  <label class="grid gap-2"
         :class="[
           [$slots['default'] ? 'grid-cols-[max-content_1fr]' : 'grid-cols-1'],
         ]">
    <span class="flex items-center transition-colors duration-300"
          :class="[
            { 'text-neutral-800 dark:text-neutral-200': !isDisabled, 'text-neutral-400 dark:text-neutral-600': isDisabled },
            { '!text-red-500':invalid },
          ]">
      <LabelError :invalid />
      <span class="font-medium">
        {{ value }}
      </span>
    </span>

    <template v-if="$slots.default">
      <span class="block">
        <slot />
      </span>
      <span v-if="$slots.end" class="block col-span-2">
        <slot name="end" />
      </span>
    </template>
  </label>
</template>
