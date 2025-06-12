<script setup>
import { onMounted, ref } from "vue";
import { templateRef } from "@vueuse/core";

defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
});

defineEmits(["update:modelValue"]);

const input = templateRef();

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
<input ref="input"
       class="p-inputtext p-component p-inputnumber-input"
       :value="modelValue"
       @input="$emit('update:modelValue', $event.target.value)">
</template>
