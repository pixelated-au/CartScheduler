<script setup lang="ts">
import { useDarkMode } from "@/Composables/useDarkMode.js";

const { colorMode, toggleDarkMode } = useDarkMode();
</script>

<template>
  <button id="theme-toggle"
          ref="themeToggle"
          type="button"
          class="flex focus:outline-none focus:ring-1 focus:ring-neutral-500 rounded-full p-2 dark:bg-panel-dark"
          aria-label="Toggle dark mode"
          @click="toggleDarkMode()">
    <transition mode="out-in">
      <DarkModeLabel v-if="colorMode === 'dark'"
                     label="dark theme"
                     icon="iconify mdi--moon-and-stars" />
      <DarkModeLabel v-else-if="colorMode === 'light'"
                     label="light theme"
                     icon="iconify mdi--weather-sunny" />
      <DarkModeLabel v-else
                     label="system theme"
                     icon="iconify mdi--cellphone iconify sm:mdi--computer"
                     class="text-neutral-400 dark:text-neutral-400" />
    </transition>
  </button>
</template>

<!--suppress CssUnusedSymbol -->
<style scoped>
.v-enter-active,
.v-leave-active {
    transition: all 0.25s ease;
}

.v-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.v-leave-to {
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
