import { fileURLToPath, URL } from "node:url";
import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vitest/config";

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: "jsdom",
    setupFiles: ["./resources/js/__tests__/setup.ts"],
    coverage: {
      provider: "v8",
      reporter: ["text", "json", "html"],
      exclude: [
        "node_modules/**",
        "resources/js/bootstrap.js",
        "resources/js/primevue-customisations.js",
      ],
    },
    include: ["resources/js/**/*.{test,spec}.{js,ts,jsx,tsx}"],
    exclude: ["node_modules/**", ".git/**"],
  },
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
    },
  },
});
