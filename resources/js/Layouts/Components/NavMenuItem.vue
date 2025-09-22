<script setup lang="ts">
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";
import type { MenuItem } from "@/Layouts/Components/Composables/useNavEvents";

const { item } = defineProps<{
  item: MenuItem;
}>();

defineEmits<{
  "click": Event;
  "command": Event;
}>();

const { closeMobileNav } = useNavEvents();

const isActive = (routeName: string | undefined) => route().current() === routeName;
</script>

<template>
  <Link v-if="item.href"
        :href="item.href"
        class="flex items-center px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
        :class="[
          isActive(item.routeName)
            ? '!font-bold underline underline-offset-4 decoration-dashed after:iconify after:mdi--chevron-left'
            : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
        ]"
        @click="closeMobileNav()">
    <span v-if="item.icon" :class="item.icon" class="text-xs me-1"/>
    {{ item.label }}
  </Link>
  <button v-else
          @click="() => { item.command && item.command(); $emit('command') }"
          class="flex items-center gap-1  px-3 py-2 w-full text-base font-medium text-left !text-current rounded-md transition-colors duration-150 ease-in-out hover:bg-neutral-200 dark:hover:bg-neutral-700">
    <span v-if="item.icon" :class="item.icon" class="text-xs"/>
    {{ item.label }}
  </button>
</template>
