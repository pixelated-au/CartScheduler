<script setup>
import { computed, watch, ref } from "vue";
import { promiseTimeout } from "@vueuse/core";

const props = defineProps({
    message: {
        type: String,
        default: undefined,
    },
});

const trimmedMessage = computed(() => (typeof props.message === "string") ? props.message.trim() : undefined);

const error = ref(undefined);

watch(trimmedMessage, async (message) => {
    if (message) {
        error.value = message;
        return;
    }
    await promiseTimeout(1000);
    error.value = undefined;
});
</script>

<template>
<div class="grid items-center font-medium transition-[grid-template-rows] duration-150 overflow-hidden mt-2"
     :class="[!trimmedMessage ? 'grid-rows-[0fr] delay-150' : 'grid-rows-[1fr] delay-0' ]">
  <div class="text-sm text-red-500 overflow-hidden transition-opacity duration-150"
       :class="[!trimmedMessage ? 'opacity-0 delay-0' : 'opacity-100 delay-150']">
    {{ error }}
  </div>
</div>
</template>
