<script setup>
defineProps({
    invalid: Boolean,
});
</script>

<template>
<span class="grid items-center font-medium transition-[grid-template-columns] duration-300"
      :class="[!invalid ? 'grid-cols-[0fr] delay-300' : 'grid-cols-[1fr] delay-0' ]">
  <span class="flex items-center overflow-hidden transition-transform duration-300"
        :class="[!invalid ? 'scale-0 delay-0' : 'scale-100 delay-300 ease-bounce-in']">
    <span class="iconify mdi--warning-circle-outline mr-1"></span>
  </span>
</span>
</template>
