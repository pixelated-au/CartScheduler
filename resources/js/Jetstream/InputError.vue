<script setup lang="ts">
import { promiseTimeout } from "@vueuse/core";
import { computed, watch, ref } from "vue";

const { message } = defineProps<{
  message?: string;
}>();

const trimmedMessage = computed(() => message?.trim() || undefined);

const error = ref();

watch(trimmedMessage, async (msg) => {
  if (msg) {
    error.value = msg;
    return;
  }
  await promiseTimeout(1000);
  error.value = undefined;
});
</script>

<template>
  <div class="grid items-center font-medium transition-[grid-template-rows] duration-150"
       :class="[!trimmedMessage ? 'grid-rows-[0fr] delay-150' : 'grid-rows-[1fr] delay-0' ]">
    <div class="text-sm transition-opacity duration-150 text-warning"
         :class="[!trimmedMessage ? 'opacity-0 delay-0' : 'opacity-100 delay-150']">
      {{ error }}
    </div>
  </div>
</template>
