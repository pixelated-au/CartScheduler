<script setup lang="ts">
import { useElementBounding } from "@vueuse/core";
import { computed, useTemplateRef, watch } from "vue";
import { useDarkMode } from "@/Composables/useDarkMode.js";

const { colorMode, setMode } = useDarkMode();

const el = useTemplateRef("themeToggle");
const op = useTemplateRef("op");
const { top, left, width, height } = useElementBounding(el);
const clip = computed(() => `circle(0% at ${left.value + (width.value / 2)}px ${top.value + (height.value / 2)}px)`);

watch(clip, (val) => {
  document.documentElement.style.setProperty("--clip", val);
});

watch(colorMode, (val) => {
  console.log("COLOR MODE:", val);
});

const toggle = (event: Event) => {
  op.value?.toggle(event);
};

const menu = [
  { label: "Dark", icon: "iconify mdi--moon-and-stars", command: () => setMode("dark") },
  { label: "Light", icon: "iconify mdi--weather-sunny", command: () => setMode("light") },
  { label: "System", icon: "iconify mdi--sun-moon-stars", command: () => setMode("auto") },
];
</script>

<template>
  <button id="theme-toggle"
          ref="themeToggle"
          type="button"
          class="flex items-center justify-center hover:bg-neutral-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-1 focus:ring-neutral-300 dark:focus:ring-neutral-700 rounded-md p-2.5"
          @click="toggle">
    <span v-if="colorMode === 'dark'" class="iconify mdi--moon-and-stars" />
    <span v-else-if="colorMode === 'light'" class="iconify mdi--weather-sunny" />
    <span v-else class="iconify mdi--sun-moon-stars text-neutral-400 dark:text-neutral-500" />
  </button>

  <PMenu ref="op" :model="menu" class="min-w-10" popup>
    <template #item="{ item }">
      <div class="flex items-center gap-2 py-1 px-3">
        <span :class="item.icon"/>
        <span class="label">{{item.label}}</span>
      </div>
    </template>
  </PMenu>
</template>

<style lang="css">
@media (prefers-reduced-motion: no-preference) {
::view-transition-old(root) {
    animation-delay: 500ms;
}

::view-transition-new(root) {
    animation: circle-in 500ms;
}
}

@keyframes circle-in {
    from {
        transform: translate3d(0, 0, 0); /* To improve performance on clip-path animation */
        /*noinspection CssUnresolvedCustomProperty*/
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
