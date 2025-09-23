<script setup lang="ts">
import { useElementBounding } from "@vueuse/core";
import { computed, ref, useTemplateRef, watch } from "vue";
import { useDarkMode } from "@/Composables/useDarkMode.js";

const { colorMode, toggleDarkMode } = useDarkMode();

const el = useTemplateRef("themeToggle");

const { top, left, width, height } = useElementBounding(el);
const clip = computed(() => `circle(0% at ${left.value + (width.value / 2)}px ${top.value + (height.value / 2)}px)`);

watch(clip, (val) => {
  document.documentElement.style.setProperty("--clip", val);
});

const isLabelShowing = ref(false);
</script>

<template>
  <button id="theme-toggle"
          ref="themeToggle"
          type="button"
          class="flex focus:outline-none focus:ring-1 focus:ring-neutral-300 dark:focus:ring-neutral-700 rounded-full p-2 dark:bg-panel-dark"
          aria-label="Toggle dark mode"
          @click="toggleDarkMode()">
    <transition name="slide-up" mode="out-in">
      <div class="node" v-if="colorMode === 'dark'"><DarkModeLabel v-model="isLabelShowing" label="dark theme" icon="iconify mdi--moon-and-stars" /></div>
      <div class="node" v-else-if="colorMode === 'light'"><DarkModeLabel v-model="isLabelShowing" label="light theme" icon="iconify mdi--weather-sunny" /></div>
      <div class="node" v-else><DarkModeLabel v-model="isLabelShowing" label="system theme" icon="iconify mdi--sun-moon-stars" class="text-neutral-400 dark:text-neutral-400" /></div>
    </transition>
  </button>
</template>

<!--suppress CssUnusedSymbol -->
<style scoped>
.node {
    transition: all 0.25s ease;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}
</style>

<style>
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
