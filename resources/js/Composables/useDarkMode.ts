import { useColorMode } from "@vueuse/core";
import { computed, nextTick } from "vue";

/**
 * A composable for managing dark mode state in a Vue application
 * @returns Object containing dark mode state and toggle function
 */
export function useDarkMode() {
  const { system, store } = useColorMode();

  const colorMode = computed(() => store.value === "auto" ? system.value : store.value);

  const isDarkMode = computed(() => colorMode.value === "dark");

  const setMode = (mode: string) => {
    nextTick(() => {
      if (mode) {
        store.value = mode;
        return;
      }
      store.value = isDarkMode.value ? "light" : "dark";
    });
  };

  const toggleDarkMode = (mode?: "light" | "dark") => {
    if (!document.startViewTransition) {
      setMode(mode);
      return;
    }

    document.startViewTransition(() => {
      setMode(mode);
    });

  };

  return {
    isDarkMode,
    toggleDarkMode,
  };
}
