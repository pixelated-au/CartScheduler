<script setup lang="ts">
import { useElementBounding } from "@vueuse/core";
import { computed, useTemplateRef, watch } from "vue";
import { useDarkMode } from "@/Composables/useDarkMode.js";

const { colorMode, toggleDarkMode } = useDarkMode();

const el = useTemplateRef("themeToggle");

const { top, left, width, height } = useElementBounding(el);
const clip = computed(() => `circle(0% at ${left.value + (width.value / 2)}px ${top.value + (height.value / 2)}px)`);

watch(clip, (val) => {
  document.documentElement.style.setProperty("--clip", val);
});
</script>

<template>
  <button id="theme-toggle"
          ref="themeToggle"
          type="button"
          class="flex focus:outline-none focus:ring-1 focus:ring-neutral-300 dark:focus:ring-neutral-700 rounded-full p-2 dark:bg-panel-dark"
          aria-label="Toggle dark mode"
          @click="toggleDarkMode()">
    <div class="flex justify-center items-center size-6">
      <DarkModeLabel :show="colorMode === 'dark'" label="dark theme" icon="iconify mdi--moon-and-stars" />
      <DarkModeLabel :show="colorMode === 'light'" label="light theme" icon="iconify mdi--weather-sunny" />
      <DarkModeLabel :show="colorMode === 'auto'"
                     label="system theme"
                     icon="iconify mdi--sun-moon-stars"
                     class="text-neutral-400 dark:text-neutral-400" />
    </div>
  </button>
</template>

<style lang="css">
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
