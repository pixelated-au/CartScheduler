<script setup>
import JetInput from "@/Jetstream/Input.vue";
import {useVModel} from "@vueuse/core";
import {computed, ref} from 'vue'

const props = defineProps({
  modelValue: [Number, String],
    inputClass: String,
})

const myModel = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const emit = defineEmits(['update:modelValue'])

const inputDescription = ref(null)
const doEditDescription = ref(false);
const hideDescription = () => doEditDescription.value = false;
</script>

<template>
    <div v-if="!doEditDescription" class="cursor-pointer"
         @click="doEditDescription = true"><span class="underline decoration-dashed">{{ modelValue }}</span>
    </div>
    <JetInput v-else v-model="myModel" autofocus class="px-2 py-1" :class="inputClass" :ref="inputDescription" @blur="hideDescription" @keydown.esc="hideDescription"/>
</template>

<style scoped>

</style>
