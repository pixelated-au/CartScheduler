<script setup>
import { useElementBounding } from "@vueuse/core";
import { useTemplateRef, computed, watchEffect } from "vue";
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
          :style="`--clipp: ${clip};`"
          class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5"
          @click="toggleDarkMode()">
    <!--                @click="toggleDarkMode"> -->
    <svg id="theme-toggle-dark-icon"
         :class="{ hidden: !isDarkMode }"
         class="w-5 h-5"
         fill="currentColor"
         viewBox="0 0 20 20"
         xmlns="http://www.w3.org/2000/svg">
      <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
    </svg>
    <svg id="theme-toggle-light-icon"
         :class="{ hidden: isDarkMode }"
         class="w-5 h-5"
         fill="currentColor"
         viewBox="0 0 20 20"
         xmlns="http://www.w3.org/2000/svg">
      <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
            fill-rule="evenodd"
            clip-rule="evenodd"></path>
    </svg>
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
