<script setup lang="ts">
import useMenuItems from "./Composables/useMenuItems";

const { hasAdminMenu, mainMenuItems } = useMenuItems();
</script>

<template>
  <div class="flex items-center sm:ml-6 sm:space-x-4">
    <template v-for="item in mainMenuItems" :key="'desktop-main-' + item.label">
      <NavSubmenu v-if="hasAdminMenu && item.isDropdown" :item="item" position="start" />
      <NavMenuItem v-if="!item.isDropdown" :item="item" />
    </template>
  </div>
</template>
