<script setup>
import { onMounted } from "vue";
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
    if (input.value?.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

// TODO, this file is redundant. We need to remove it
</script>

<template>
<PInputText ref="input"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"/>
</template>
