<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";

export type Item = {
  label: string;
  routeName?: string | undefined;
  href?: string;
  isDropdown?: boolean;
  submenu?: Item[];
  command?: () => void;
  hasUpdate?: boolean;
};

defineProps<{
  item: Item;
}>();

defineEmits<{
  "click": [Item["label"]];
}>();

const breakpoints = useBreakpoints(breakpointsTailwind);
const page = usePage();

const isMobile = breakpoints.smaller("sm");

const mobileNavOpen = ref(false);
const mobileUserMenuOpen = ref(false);
const openMobileSubmenus = ref<Record<string, boolean>>({}); // E.g., { 'Administration': false }
const desktopAdminDropdownOpen = ref(false);
const desktopUserDropdownOpen = ref(false);

// Close all menus when route changes
watch(() => page.url, () => {
  mobileNavOpen.value = false;
  mobileUserMenuOpen.value = false;
  desktopAdminDropdownOpen.value = false;
  desktopUserDropdownOpen.value = false;
  Object.keys(openMobileSubmenus.value).forEach((key) => openMobileSubmenus.value[key] = false);
});

const toggleMobileSubmenu = (label: Item["label"]) => {
  openMobileSubmenus.value[label] = !openMobileSubmenus.value[label];
};

// --- Animation Hooks (Tailwind doesn't need JS for simple opacity/transform, but height needs it) ---
const onBeforeEnter = (el: Element) => {
  const style = (el as HTMLElement).style;
  style.opacity = "0";
  style.maxHeight = "0px";
};
const onEnter = async (el: Element, done: () => void) => {
  await nextTick(() => {
    const style = (el as HTMLElement).style;
    style.transitionProperty = "max-height, opacity";
    style.transitionDuration = "300ms";
    style.transitionTimingFunction = "ease-out";
    style.opacity = "1";
    style.maxHeight = (el as HTMLElement).scrollHeight + "px";
  });
  // Call done when transition finishes
  el.addEventListener("transitionend", done, { once: true });
};
const onAfterEnter = (el: Element) => {
  const style = (el as HTMLElement).style;
  style.maxHeight = ""; // Use 'auto' or remove for content to dictate height
  style.transitionProperty = "";
  style.transitionDuration = "";
  style.transitionTimingFunction = "";
};
const onBeforeLeave = (el: Element) => {
  const style = (el as HTMLElement).style;
  style.transitionProperty = "max-height, opacity";
  style.transitionDuration = "300ms";
  style.transitionTimingFunction = "ease-in";
  style.maxHeight = (el as HTMLElement).scrollHeight + "px";
  style.opacity = "1"; // Start fully visible
};
const onLeave = async (el: Element, done: () => void) => {
  await nextTick(() => {
    const style = (el as HTMLElement).style;
    style.maxHeight = "0px";
    style.opacity = "0";
  });
  el.addEventListener("transitionend", done, { once: true });
};
const onAfterLeave = (el: Element) => {
  const style = (el as HTMLElement).style;
  style.maxHeight = "";
  style.opacity = "";
  style.transitionProperty = "";
  style.transitionDuration = "";
  style.transitionTimingFunction = "";
};

// --- Event Handlers for Closing Menus ---
const handleEscapeKey = (event: KeyboardEvent) => {
  if (event.key === "Escape") {
    if (isMobile.value) {
      if (mobileNavOpen.value) mobileNavOpen.value = false;
      if (mobileUserMenuOpen.value) mobileUserMenuOpen.value = false;
      // Consider closing active mobile submenu
      const openSub = Object.keys(openMobileSubmenus.value).find((key) => openMobileSubmenus.value[key]);
      if (openSub) openMobileSubmenus.value[openSub] = false;
    } else {
      if (desktopAdminDropdownOpen.value) desktopAdminDropdownOpen.value = false;
      if (desktopUserDropdownOpen.value) desktopUserDropdownOpen.value = false;
    }
  }
};

const handleClickOutside = () => {
  if (desktopAdminDropdownOpen.value) {
    desktopAdminDropdownOpen.value = false;
  }
  if (desktopUserDropdownOpen.value) {
    desktopUserDropdownOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleEscapeKey);
  document.addEventListener("click", handleClickOutside, true); // Capture phase for reliability
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleEscapeKey);
  document.removeEventListener("click", handleClickOutside, true);
});

const isActive = (routeName: string | undefined) => route().current() === routeName;
</script>

<template>
  <div v-if="item.isDropdown">
    <button @click="toggleMobileSubmenu(item.label)"
            type="button"
            class="flex justify-between items-center px-3 py-2 w-full font-medium rounded-md transition-colors duration-150 ease-in-out hover:ring-1 ring-black/25 dark:ring-white/25"
            :aria-expanded="openMobileSubmenus[item.label] ? 'true' : 'false'"
            :aria-controls="'mobile-submenu-' + item.label">
      <span>{{ item.label }}</span>
      <span class="text-2xl duration-500 ease-in-out delay-100 iconify mdi--chevron-down transition-rotate"
            :class="{ 'rotate-180': openMobileSubmenus[item.label] }"></span>
    </button>
    <transition @before-enter="onBeforeEnter"
                @enter="onEnter"
                @after-enter="onAfterEnter"
                @before-leave="onBeforeLeave"
                @leave="onLeave"
                @after-leave="onAfterLeave">
      <div v-show="openMobileSubmenus[item.label]"
           :id="'mobile-submenu-' + item.label"
           class="overflow-hidden gap-2 pl-4 mt-3">
        <Link v-for="subItem in item.submenu"
              :key="'mobile-sub-' + subItem.label"
              :href="subItem.href as string"
              class="flex items-center px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
              :class="[
                isActive(subItem.routeName)
                  ? '!font-bold underline underline-offset-4 decoration-dashed'
                  : 'dark: hover:bg-neutral-200 dark:hover:bg-neutral-700'
              ]"
              @click="() => { mobileNavOpen = false; openMobileSubmenus[item.label] = false; }">
          <span v-if="isActive(subItem.routeName)" class="-ml-4 iconify mdi--chevron-right"></span>
          {{ subItem.label }}
        </Link>
      </div>
    </transition>
  </div>
</template>
