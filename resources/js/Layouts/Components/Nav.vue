<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import useMenuItems from "@/Layouts/Components/Composables/useMenuItems";
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";

const page = usePage();

const desktopAdminDropdownOpen = ref(false);
const desktopUserDropdownOpen = ref(false);

const { hasAdminMenu, userNavMenuItems, mainMenuItems } = useMenuItems();

const { toggleMobileNav, mobileNavOpen } = useNavEvents();

// --- Toggle Functions ---
const toggleDesktopAdminDropdown = () => {
  desktopAdminDropdownOpen.value = !desktopAdminDropdownOpen.value;
  if (desktopAdminDropdownOpen.value) desktopUserDropdownOpen.value = false;
};
const toggleDesktopUserDropdown = () => {
  desktopUserDropdownOpen.value = !desktopUserDropdownOpen.value;
  if (desktopUserDropdownOpen.value) desktopAdminDropdownOpen.value = false;
};

const isActive = (routeName: string | undefined) => route().current() === routeName;

const mobileNavIsShowing = ref(false);
const mobileMenuToggle = (isVisible: boolean) => {
  mobileNavIsShowing.value = isVisible;
  if (!isVisible) {
  }
};
</script>

<template>
  <nav class="flex flex-col justify-between relative z-50 bg-panel dark:bg-panel-dark">
    <div class="w-full  grid  items-center"
         :class="[
           mobileNavIsShowing ? 'grid-cols-[1fr_auto_auto]' : 'grid-cols-[1fr_auto]'
         ]">
      <Link :href="route('dashboard')"
            class="m-4">
        <JetApplicationMark class="block w-auto h-9" />
      </Link>

      <!-- todo      NOW fix the desktop mode menu -->

      <DarkMode v-show="mobileNavIsShowing" class="me-2" />

      <!-- Mobile Main Menu Toggle (Hamburger) -->
      <div class="flex items-center sm:hidden">
        <!--        <NavHamburgerButton /> -->
        <NavHamburgerButton :is-active="mobileNavIsShowing"
                            :aria-expanded="mobileNavIsShowing ? 'true' : 'false'"
                            @click="toggleMobileNav"/>
      </div>
    </div>

    <div class="hidden sm:flex justify-between items-center h-16">
      <div class="flex items-center">
        <!-- Desktop Main Menu -->
        <div class="sm:ml-6 sm:space-x-4">
          <template v-for="item in mainMenuItems" :key="item.label">
            <Link v-if="!item.isDropdown"
                  :href="item.href as string"
                  class="px-3 py-2 rounded-md font-medium transition-colors duration-150 ease-in-out !text-current"
                  :class="[
                    isActive(item.routeName)
                      ? '!font-bold underline underline-offset-4 decoration-dashed'
                      : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                  ]"
                  :aria-current="isActive(item.routeName) ? 'page' : undefined">
              {{ item.label }}
            </Link>

            <!-- Desktop Dropdown -->
            <div v-if="item.isDropdown && item.label === 'Administration'" class="relative" ref="adminDropdownRef">
              <button @click="toggleDesktopAdminDropdown"
                      type="button"
                      class="flex items-center px-3 py-2 font-medium text-current rounded-md transition-colors duration-150 ease-in-out hover:underline decoration-dotted relative"
                      :class="[
                        desktopAdminDropdownOpen || item.submenu && item.submenu.some(subItem => isActive(subItem.routeName))
                          ? '!font-bold underline underline-offset-4 decoration-dotted'
                          : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                      ]"
                      aria-haspopup="true"
                      :aria-expanded="desktopAdminDropdownOpen ? 'true' : 'false'"
                      aria-controls="desktop-admin-menu">
                <span class="relative"
                      :class="
                        { 'before:block before:iconify before:mdi--bell-circle before:absolute before:-top-1 before:-left-4 before:size-4 before:rounded-full before:text-help dark:before:text-help-light': item.hasUpdate }
                      ">
                  {{ item.label }}
                </span>
                <span class="text-lg duration-500 ease-in-out delay-100 iconify mdi--chevron-down transition-rotate"
                      :class="{ 'rotate-180 !delay-0 !duration-300': desktopAdminDropdownOpen }"></span>
              </button>
              <NavMenuTransition>
                <div v-show="desktopAdminDropdownOpen"
                     id="desktop-admin-menu"
                     class="overflow-hidden absolute right-0 z-50 py-1 mt-2 w-48 bg-white rounded-md ring-1 ring-black ring-opacity-5 shadow-lg origin-top-right dark:bg-neutral-700 focus:outline-none"
                     role="menu"
                     aria-orientation="vertical"
                     :aria-labelledby="item.label + '-button'">
                  <Link v-for="subItem in item.submenu"
                        :key="subItem.label"
                        :href="subItem.href as string"
                        class="flex items-center px-6 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600"
                        :class="[
                          { '!no-underline': isActive(subItem.routeName) },
                          { 'decoration-help dark:decoration-help-light': subItem.hasUpdate }
                        ]"
                        role="menuitem"
                        @click="desktopAdminDropdownOpen = false">
                    <span v-if="isActive(subItem.routeName)"
                          class="-ml-4 iconify mdi--chevron-right"
                          :class="{ 'text-help dark:text-help-light': subItem.hasUpdate }"></span>
                    <span class="relative"
                          :class="[
                            { '!font-bold underline underline-offset-4 decoration-dotted': isActive(subItem.routeName) },
                            { 'decoration-help dark:decoration-help-light': subItem.hasUpdate },
                            { 'before:block before:iconify before:mdi--bell-circle before:absolute before:-top-1 before:-right-4 before:size-4 before:rounded-full before:text-help dark:before:text-help-light': subItem.hasUpdate },
                          ]">
                      {{ subItem.label }}
                    </span>
                  </Link>
                </div>
              </NavMenuTransition>
            </div>
          </template>
        </div>
      </div>

      <!-- Right side: Mobile Toggles & Desktop User Menu -->
      <div class="flex justify-end items-center">
        <!-- Desktop User Menu -->
        <div class="hidden sm:flex sm:items-center sm:ml-6" ref="userDropdownRef">
          <div class="relative">
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
        </div>
      </div>
    </div>

    <!-- Mobile Main Navigation (collapsible) -->
    <NavMenuTransition @visibility="mobileMenuToggle">
      <div v-show="mobileNavOpen"
           class="w-full overflow-hidden sm:hidden bg-neutral-50 dark:bg-sub-panel-dark"
           id="mobile-main-menu"
           ref="mobileNavRef">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <div class="grid gap-3"
               :class="[ { 'grid-cols-[7fr_5fr]': hasAdminMenu } ]">
            <div class="p-1 dark:bg-panel-dark rounded border std-border">
              <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
                <NavSubmenu v-if="hasAdminMenu && item.isDropdown" :item="item" show-as-inline />
                <NavMenuItem v-if="!item.isDropdown" :item="item" />
              </template>

              <template v-if="!hasAdminMenu">
                <template v-for="item in userNavMenuItems" :key="'mobile-main-' + item.label">
                  <NavMenuItem :item="item" />
                </template>
              </template>
            </div>

            <div v-if="hasAdminMenu" class="p-1 dark:bg-panel-dark rounded border std-border">
              <div class="font-semibold uppercase"></div>

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
