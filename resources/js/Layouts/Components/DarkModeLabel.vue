<script setup lang="ts">
import { onMounted, ref } from "vue";

const { classes="" } = defineProps<{
  label: string;
  icon: string;
  classes?: string;
}>();

const show = defineModel<boolean>();

const showLabel = ref(false);

onMounted(() => {
  if (show.value) {
    showLabel.value = true;
  }
  setTimeout(() => {
    show.value = true;
    showLabel.value = true;

    setTimeout(() => {
      show.value = false;
      showLabel.value = false;
    }, 3000);
  }, 300);
});
</script>

<template>
  <!--  <div v-if="show" -->
  <div class="flex items-center"
       :class="[ showLabel ? '' : 'delay-[300ms]' ]">
    <span class="grid items-center font-medium transition-[grid-template-columns] duration-300 relative"
          :class="[showLabel ? 'grid-cols-[1fr] delay-0' : 'grid-cols-[0] delay-150' ]">
      <span class="flex items-center overflow-hidden transition-transform duration-300 z-10 text-sm origin-right whitespace-nowrap"
            :class="[showLabel ? 'scale-x-100 px-1 delay-0 ease-bounce-in' : 'scale-x-0 delay-0']">
        {{label}}
      </span>
    </span>
    <span class="size-6" :class="[ icon, classes ]" />
  </div>
</template>
