<script setup lang="ts">
import { computed } from "vue";
import LabelError from "@/Jetstream/LabelError.vue";

type Props<K extends string> = {
  value?: string;
  isDisabled?: boolean;
  form?: {
    errors: Record<K, string>;
  };
  errorKey?: K;
  hasError?: boolean;
};

const { value, isDisabled = false, errorKey = "", form, hasError } = defineProps<Props<string>>();

const invalid = computed(() => hasError || !!form?.errors[errorKey]);
</script>

<template>
  <label class="grid gap-2"
         :class="[
           [$slots['default'] ? 'grid-cols-[max-content_1fr]' : 'grid-cols-1'],
         ]">
    <span class="flex items-center transition-[color,gap] duration-300"
          :class="[
            { 'text-neutral-800 dark:text-neutral-200': !isDisabled, 'text-neutral-400 dark:text-neutral-600': isDisabled },
            [ invalid ? '!text-warning gap-1' : 'gap-0 delay-[0ms,300ms]' ],
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
