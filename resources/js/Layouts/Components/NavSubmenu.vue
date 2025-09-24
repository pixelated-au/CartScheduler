<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { onClickOutside } from "@vueuse/core";
import { computed, onMounted, onUnmounted, ref, useId } from "vue";
import useNavEvents from "./Composables/useNavEvents";
import type { MenuItem } from "./Composables/useNavEvents";

const { item, items, label, showAsInline = false } = defineProps<{
  item?: MenuItem;
  items?: MenuItem[];
  label?: string;
  showAsInline?: boolean;
  position: "start" | "end";
}>();

const { submenuOpen, toggleSubmenu, openSubmenus, closeNav, addEscapeHandler, removeEscapeHandler } = useNavEvents();

const submenu = computed(() => {
  if (item) {
    return item.submenu;
  }
  if (items) {
    return items;
  }
  throw Error("No submenu found");
});

const isActive = (routeName: string | undefined) => route().current() === routeName;
const id = useId();

const myLabel = computed(() => {
  if (label) {
    return label;
  }
  if (item?.label) {
    return item.label;
  }
  throw new Error("Label not set");
});

const isSubmenuOpen = submenuOpen(myLabel);
const isSubmenuItemActive = computed(() => submenu.value && submenu.value.some((subItem) => isActive(subItem.routeName)));

const toggle = () => {
  // if (cancel) cancel();
  toggleSubmenu(myLabel.value);
};

onMounted(() => {
  addEscapeHandler("mobile-nav", (event: KeyboardEvent) => {
    if (!isSubmenuOpen.value) return;
    event.preventDefault();
    closeNav(myLabel.value);
  });
});

onUnmounted(() => {
  removeEscapeHandler("mobile-nav");
});

const target = ref();

onClickOutside(target, () => {
  if (!isSubmenuOpen.value) return;

  closeNav(myLabel.value);
});
</script>

<template>
  <div class="relative">
    <button v-if="!showAsInline"
            @click="toggle"
            type="button"
            class="flex justify-between items-center w-full font-medium rounded-md transition-colors duration-150 ease-in-out hover:ring-1 ring-black/25 dark:ring-white/25"
            :class="[
              isSubmenuOpen || isSubmenuItemActive
                ? '!font-bold underline underline-offset-4 decoration-dotted'
                : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
            ]"
            :aria-expanded="openSubmenus[myLabel] ? 'true' : 'false'"
            :aria-controls="id">
      <template v-if="!$slots.button">
        <span>{{ myLabel }}</span>
        <span class="duration-500 ease-in-out delay-100 iconify mdi--chevron-down transition-rotate"
              :class="{ 'rotate-180': isSubmenuOpen }"></span>
      </template>

      <slot v-else name="button" />
    </button>

    <NavMenuTransition>
      <div v-if="showAsInline || isSubmenuOpen"
           :ref="(el) => target = el"
           :id
           class="overflow-hidden gap-2 ps-4 sm:ps-1 sm:py-1 sm:mt-2 sm:min-w-48 sm:z-50 sm:bg-white sm:rounded-md sm:ring-1 sm:ring-black sm:ring-opacity-5 sm:shadow-lg sm:origin-top-right sm:dark:bg-neutral-700 sm:focus:outline-none"
           :class="[
             position === 'start' ? 'sm:absolute sm:left-0' : 'sm:absolute sm:right-0'
           ]">
        <template v-for="subItem in submenu" :key="subItem.label">
          <Link v-if="subItem.href"
                :href="subItem.href as string"
                class="flex items-center py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current text-nowrap"
                :class="[
                  { 'px-3': !showAsInline },
                  isActive(subItem.routeName)
                    ? '!font-bold underline underline-offset-4 decoration-dashed after:iconify after:mdi--chevron-left'
                    : 'dark: hover:bg-neutral-200 dark:hover:bg-neutral-700'
                ]">
            <!--                @click=""> -->
            <span v-if="subItem.icon" :class="subItem.icon" class="text-xs me-1" />
            {{ subItem.label }}
          </Link>
          <div v-if="subItem.command"
               @click="() => { subItem.command && subItem.command(); }"
               class="border-t std-border dark:border-neutral-600 block px-4 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600 hover:underline decoration-dotted"
               role="menuitem">
            {{ subItem.label }}
          </div>
        </template>
      </div>
    </NavMenuTransition>
  </div>
</template>
