<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";
import type { Item } from "@/Layouts/Components/Composables/useNavEvents";

defineProps<{
  item: Item;
}>();

const { closeMobileNav, toggleMobileSubmenu, openMobileSubmenus } = useNavEvents();

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
    <NavMenuTransition>
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
              @click="closeMobileNav(item.label)">
          <span v-if="isActive(subItem.routeName)" class="-ml-4 iconify mdi--chevron-right"></span>
          {{ subItem.label }}
        </Link>
      </div>
    </NavMenuTransition>
  </div>
</template>
