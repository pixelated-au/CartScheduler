<script setup lang="ts">
import { onMounted, ref } from "vue";

const { classes = "" } = defineProps<{
  label: string;
  icon: string;
  classes?: string;
}>();

const showLabel = ref(true);

onMounted(() => {
  setTimeout(() => {
    showLabel.value = true;

    setTimeout(() => {
      showLabel.value = false;
    }, 3000);
  }, 200);
});
</script>

<template>
  <div class="flex items-center">
    <div class="grid items-center font-medium transition-[grid-template-columns,padding] duration-300 delay-200"
         :class="[showLabel ? 'grid-cols-[1fr] px-1' : 'grid-cols-[0fr]' ]">
      <div class="flex items-center overflow-hidden transition-transform duration-300 text-sm origin-right whitespace-nowrap"
           :class="[showLabel ? 'scale-x-100 ease-bounce-in' : 'scale-x-0']">
        {{ label }}
      </div>
    </div>
    <div class="icon size-6" :class="[ icon, classes ]" />
  </div>
</template>
