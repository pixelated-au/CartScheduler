<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import useMenuItems from "./Composables/useMenuItems";

const { userNavMenuItems } = useMenuItems();
const page = usePage();
</script>

<template>
  <NavSubmenu :items="userNavMenuItems" label="desktop-user-menu" pop-up-position="end">
    <template #button>
      <img v-if="page.props.auth.user?.profile_photo_url"
           class="w-8 h-8 rounded-full"
           :src="page.props.auth.user.profile_photo_url"
           :alt="page.props.auth.user?.name || 'User Avatar'">
      <span v-else
            class="inline-flex justify-center items-center w-8 h-8 rounded-full bg-neutral-300 dark:bg-neutral-600">
        <span class="font-medium leading-none !text-current">
          {{ page.props.auth.user?.name?.charAt(0) || "U" }}
        </span>
      </span>
    </template>
  </NavSubmenu>
</template>
