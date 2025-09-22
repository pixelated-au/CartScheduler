<script setup lang="ts">
import { ref, watch } from "vue";

const { show, classes="" } = defineProps<{
  show: boolean;
  label: string;
  icon: string;
  classes?: string;
}>();

const showLabel = ref(false);

watch(() =>show, (value) => {
  if (!value) return;

  setTimeout(() => {
    showLabel.value = true;

    setTimeout(() => {
      showLabel.value = false;
    }, 3000);
  }, 500);
});
</script>

<template>
  <div v-if="show"
       class="flex items-center transition-[gap]"
       :class="[ showLabel ? 'gap-2' : 'gap-0 delay-[0ms,300ms]' ]">
    <!--    <span class="grid items-center font-medium transition-[grid-template-columns] duration-300 relative" -->
    <span class="grid items-center font-medium transition-[grid-template-columns] duration-300 relative"
          :class="[!showLabel ? 'grid-cols-[0fr] delay-150' : 'grid-cols-[1fr] delay-0' ]">
      <span class="flex items-center overflow-hidden transition-transform duration-300 z-10 text-sm origin-right whitespace-nowrap"
            :class="[!showLabel ? 'scale-x-0 delay-0' : 'scale-x-100 ps-1 delay-0 ease-bounce-in']">
        {{label}}
      </span>
    </span>
    <span :class="[ icon, classes ]" />
  </div>
</template>
