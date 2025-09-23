<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { onMounted, onUnmounted, ref } from "vue";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import useMenuItems from "@/Layouts/Components/Composables/useMenuItems";
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";

const page = usePage();
const breakpoints = useBreakpoints(breakpointsTailwind);
const isNotMobile = breakpoints.greaterOrEqual("sm");

const { hasAdminMenu, userNavMenuItems, mainMenuItems } = useMenuItems();
const { toggleMobileNav, mobileNavOpen, closeMobileNav, addEscapeHandler, removeEscapeHandler } = useNavEvents();

const mobileNavIsShowing = ref(false);
const mobileMenuToggle = (isVisible: boolean) => {
  mobileNavIsShowing.value = isVisible;
};

onMounted(() => {
  addEscapeHandler("mobile-nav", (event: KeyboardEvent) => {
    if (!mobileNavIsShowing.value) return;
    event.preventDefault();
    closeMobileNav();
  });
});

onUnmounted(() => {
  removeEscapeHandler("mobile-nav");
});
</script>

<template>
  <nav class="justify-between relative z-50 bg-panel dark:bg-panel-dark">
    <div class="w-full grid items-center"
         :class="[ mobileNavIsShowing ? 'grid-cols-[1fr_auto_auto]' : 'grid-cols-[1fr_auto] sm:grid-cols-[auto_1fr_auto_auto]' ]">
      <Link :href="route('dashboard')" class="m-4">
        <JetApplicationMark class="block w-auto h-9" />
      </Link>

      <!--      Desktop Menu -->
      <div class="hidden sm:flex justify-between items-center grow">
        <div class="flex items-center sm:ml-6 sm:space-x-4">
          <!-- Desktop Main Menu -->
          <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
            <NavSubmenu v-if="hasAdminMenu && item.isDropdown" :item="item" position="start" />
            <NavMenuItem v-if="!item.isDropdown" :item="item" />
          </template>
        </div>
      </div>
      <!--      END Desktop Menu -->

      <DarkMode v-show="isNotMobile || mobileNavIsShowing" class="me-2" />

      <!-- Mobile Main Menu Toggle (Hamburger) -->
      <div class="flex items-center sm:hidden">
        <NavHamburgerButton :is-active="mobileNavIsShowing"
                            :aria-expanded="mobileNavIsShowing ? 'true' : 'false'"
                            @click="toggleMobileNav" />
      </div>

      <!-- Desktop User Menu -->
      <div class="hidden sm:flex sm:items-center justify-end items-center sm:me-4">
        <NavSubmenu :items="userNavMenuItems" label="desktop-user-menu" position="end">
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

        <!--        <div class="relative">
          <button @click="toggleDesktopUserDropdown"
          type="button"
          class="flex rounded-full transition"
          id="user-menu-button"
          aria-haspopup="true"
          :aria-expanded="desktopUserDropdownOpen.toString() ? 'true' : 'false'">
          <span class="sr-only">Open user menu</span>
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
          </button>
          <NavMenuTransition>
          <div v-show="desktopUserDropdownOpen"
          class="overflow-hidden absolute right-0 z-50 py-1 mt-2 w-48 bg-white rounded-md ring-1 ring-black ring-opacity-5 shadow-lg origin-top-right dark:bg-neutral-700 focus:outline-none"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="user-menu-button">
          <template v-for="userItem in userNavMenuItems" :key="userItem.label">
          <Link v-if="userItem.href"
          :href="userItem.href"
          class="block px-4 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600"
          :class="{ 'bg-neutral-100 dark:bg-neutral-600 font-semibold': isActive(userItem.routeName) }"
          role="menuitem"
          @click="desktopUserDropdownOpen = false">
          {{ userItem.label }}
          </Link>
          <div v-if="userItem.command"
          @click="() => { userItem.command && userItem.command(); desktopUserDropdownOpen = false; }"
          class="border-t std-border dark:border-neutral-600 block px-4 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600 hover:underline decoration-dotted"
          role="menuitem">
          {{ userItem.label }}
          </div>
          </template>
          </div>
          </NavMenuTransition>
          </div>
        -->
      </div>
      <!-- END Desktop User Menu -->
    </div>

    <!-- Mobile Main Navigation (collapsible) -->
    <NavMenuTransition @visibility="mobileMenuToggle">
      <div v-show="mobileNavOpen"
           class="w-full overflow-hidden sm:hidden bg-neutral-50 dark:bg-sub-panel-dark"
           id="mobile-main-menu">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <div class="grid gap-3"
               :class="[ { 'grid-cols-[7fr_5fr]': hasAdminMenu } ]">
            <div class="p-1 dark:bg-panel-dark rounded border std-border">
              <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
                <NavSubmenu v-if="hasAdminMenu && item.isDropdown" :item="item" position="start" show-as-inline />
                <NavMenuItem v-if="!item.isDropdown" :item="item" />
              </template>

              <template v-if="!hasAdminMenu">
                <template v-for="item in userNavMenuItems" :key="'mobile-main-' + item.label">
                  <NavMenuItem :item="item" />
                </template>
              </template>
            </div>

            <div v-if="hasAdminMenu" class="p-1 dark:bg-panel-dark rounded border std-border">
              <template v-for="item in userNavMenuItems" :key="'mobile-main-' + item.label">
                <NavMenuItem :item="item" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </NavMenuTransition>
  </nav>
</template>
