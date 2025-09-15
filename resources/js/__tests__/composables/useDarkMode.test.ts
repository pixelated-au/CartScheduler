import { beforeEach, describe, expect, it, vi } from "vitest";
import { ref } from "vue";
import { useDarkMode } from "@/Composables/useDarkMode";

vi.mock("@vueuse/core", () => {
  return ({
    useColorMode: () => ({
      store: ref("light"),
      system: ref("light"),
    }),
  });
});

type Callback = ((callbackOptions?: ViewTransitionUpdateCallback | undefined) => ViewTransition) | undefined;

const viewTransitionFunction: { value: Callback; writable: boolean } = {
  value: undefined,
  writable: true,
};

describe("useDarkMode", () => {
  beforeEach(() => {
    localStorage.clear();
    Object.defineProperty(document, "startViewTransition", viewTransitionFunction);
    vi.clearAllMocks();
  });

  it("initializes with light mode by default", () => {
    const { isDarkMode } = useDarkMode();
    expect(isDarkMode.value).toBe(false);
  });

  it("handles viewTransition", async () => {
    document.startViewTransition = (callback: (callbackOptions?: ViewTransitionUpdateCallback | undefined) => ViewTransition) => callback();

    const { isDarkMode, toggleDarkMode } = useDarkMode();
    expect(isDarkMode.value).toBe(false);
    await toggleDarkMode("dark");
    expect(isDarkMode.value).toBe(true);
  });

  it("toggles between light and dark mode", async () => {
    const { isDarkMode, toggleDarkMode } = useDarkMode();
    expect(isDarkMode.value).toBe(false);
    await toggleDarkMode("dark");
    expect(isDarkMode.value).toBe(true);
    await toggleDarkMode();
    expect(isDarkMode.value).toBe(false);
  });
});
