<script setup lang="ts">
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";

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
  "click": Event;
}>();

const { closeMobileNav } = useNavEvents();

const isActive = (routeName: string | undefined) => route().current() === routeName;
</script>

<template>
  <Link v-if="!item.isDropdown && item.href"
        :href="item.href"
        class="block px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
        :class="[
          isActive(item.routeName)
            ? '!font-bold underline underline-offset-4 decoration-dashed'
            : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
        ]"
        @click="closeMobileNav()">
    <span v-if="isActive(item.routeName)" class="-ml-4 iconify mdi--chevron-right"></span>
    {{ item.label }}
  </Link>
</template>
