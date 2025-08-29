<script setup lang="ts">
import { useElementBounding } from "@vueuse/core";
import { computed, useTemplateRef, watchEffect } from "vue";
import { useDarkMode } from "@/Composables/useDarkMode.js";

const { isDarkMode, toggleDarkMode } = useDarkMode();

const el = useTemplateRef("themeToggle");
const { top, left, width, height } = useElementBounding(el);
const clip = computed(() => `circle(0% at ${left.value + (width.value / 2)}px ${top.value + (height.value / 2)}px)`);

watchEffect(() => {
  const root = document.documentElement;
  root.style.setProperty("--clip", clip.value);
});
</script>

<template>
  <button id="theme-toggle"
          ref="themeToggle"
          type="button"
          :style="`--clip: ${clip};`"
          class="flex items-center justify-center text-gray-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-4 focus:ring-neutral-200 dark:focus:ring-neutral-700 rounded-md p-2.5"
          @click="toggleDarkMode()">
    <KeepAlive :include="[]">
      <span v-if="isDarkMode" class="iconify ri--moon-line" />
      <span v-else class="iconify ri--sun-line" />
    </KeepAlive>
  </button>
</template>

<style lang="css">
:root {
    --clip: circle(0% at 50% 50%);;
}

::view-transition-old(root) {
    animation-delay: 500ms;
}

::view-transition-new(root) {
    animation: circle-in 500ms;
}

@keyframes circle-in {
    from {
        clip-path: var(--clip);
    }
    to {
        clip-path: circle(120% at 50% 0%);
    }
}

:root {
    @media (prefers-color-scheme: light) {
        color-scheme: light;
    }

    @media (prefers-color-scheme: dark) {
        color-scheme: dark;
    }
}

html {
    color-scheme: light;

    /*noinspection ALL*/

    &.dark {
        color-scheme: dark;
    }
}
</style>
