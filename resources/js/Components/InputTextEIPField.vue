<script setup>
import { computed, ref } from "vue";
import JetInput from "@/Jetstream/Input.vue";

const props = defineProps({
  modelValue: [Number, String],
  inputClass: String,
  emptyValue: {
    type: String,
    default: "",
  },
});

const myModel = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const emit = defineEmits(["update:modelValue"]);

const inputDescription = ref(null);
const doEditDescription = ref(false);
const hideDescription = () => doEditDescription.value = false;
</script>

<template>
  <div
      v-if="!doEditDescription"
      class="cursor-pointer"
      @click="doEditDescription = true">
    <span
        class="underline decoration-dashed ml-1">
      {{ modelValue?.trim() || emptyValue }}
    </span>
  </div>
  <JetInput
      v-else
      v-model="myModel"
      autofocus
      class="px-2 py-1"
      :class="inputClass"
      :ref="inputDescription"
      @blur="hideDescription"
      @keydown.esc="hideDescription"/>
</template>

<style scoped>

</style>
