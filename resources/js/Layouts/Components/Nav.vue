<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onBeforeMount, ref } from "vue";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import useNavEvents from "@/Layouts/Components/Composables/useNavEvents";
import type{ Item } from "@/Layouts/Components/NavMenuItem.vue";

const page = usePage();

type Permissions = {
  canAdmin?: boolean;
  canEditSettings?: boolean;
};

let permissions: Permissions;

onBeforeMount(() => {
  permissions = page.props.pagePermissions as Permissions;
});

const desktopAdminDropdownOpen = ref(false);
const desktopUserDropdownOpen = ref(false);

const hasUpdate = computed(() => page.props.hasUpdate as boolean); // Keep if used for update indicators

const mainMenuItems = computed(() => {
  const items: Item[] = [{ label: "Dashboard", routeName: "dashboard", href: route("dashboard") }];

  if (!permissions?.canAdmin) return items;

  const adminSubmenu: Item[] = [
    { label: "Admin Dashboard", routeName: "admin.dashboard", href: route("admin.dashboard") },
    { label: "Users", routeName: "admin.users.index", href: route("admin.users.index") },
    { label: "Locations", routeName: "admin.locations.index", href: route("admin.locations.index") },
    { label: "Reports", routeName: "admin.reports.index", href: route("admin.reports.index") },
  ];
  if (permissions.canEditSettings) {
    adminSubmenu.push({
      label: "Settings",
      routeName: "admin.settings",
      href: route("admin.settings"),
      hasUpdate: hasUpdate.value,
    });
  }
  items.push({
    label: "Administration",
    routeName: undefined,
    hasUpdate: hasUpdate.value, // Example if an entire section can have an update
    isDropdown: true,
    submenu: adminSubmenu,
  });
  return items;
});

const userNavMenuItems = computed(() => {
  if (!page.props.auth || !page.props.auth.user) return [];
  const items: Item[] = [
    { label: "Profile", routeName: "profile.show", href: route("profile.show") },
  ];
  if (page.props.enableUserAvailability) {
    items.push({ label: "Availability", routeName: "user.availability", href: route("user.availability") });
  }
  items.push({ label: "Log Out", command: () => logout() });
  return items;
});

const {
  closeAllNav,
  toggleMobileNav,
  toggleMobileUserMenu,
  closeMobileUserMenu,
  mobileNavOpen,
  mobileUserMenuOpen,
} = useNavEvents();

const logout = () => {
  router.post(route("logout"), {}, {
    onFinish: () => {
      closeAllNav();

      desktopAdminDropdownOpen.value = false;
      desktopUserDropdownOpen.value = false;
    },
  });
};

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
</script>

<template>
  <nav class="relative z-50 bg-panel dark:bg-panel-dark">
    <div class="container">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <Link :href="route('dashboard')" class="flex-shrink-0">
            <JetApplicationMark class="block w-auto h-9" />
          </Link>

          <!-- Desktop Main Menu -->
          <div class="hidden sm:flex sm:ml-6 sm:space-x-4">
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
          <DarkMode />

          <!-- Mobile User Menu Toggle -->
          <div class="mr-3 sm:hidden">
            <button @click="toggleMobileUserMenu"
                    type="button"
                    class="p-2 rounded-md transition hover:text-white hover:bg-neutral-700"
                    aria-controls="mobile-user-menu"
                    :aria-expanded="mobileUserMenuOpen ? 'true' : 'false'">
              <span class="sr-only">Open user menu</span>
              <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>

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
                    {{ page.props.auth.user?.name?.charAt(0) || 'U' }}
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

          <!-- Mobile Main Menu Toggle (Hamburger) -->
          <div class="flex items-center -mr-2 sm:hidden">
            <button @click="toggleMobileNav"
                    type="button"
                    class="p-2 rounded-md transition hover:text-white hover:bg-neutral-700"
                    aria-controls="mobile-main-menu"
                    :aria-expanded="mobileNavOpen ? 'true' : 'false'">
              <span class="sr-only">Open main menu</span>
              <svg v-if="!mobileNavOpen"
                   class="block w-6 h-6"
                   xmlns="http://www.w3.org/2000/svg"
                   viewBox="0 0 24 24"
                   stroke="currentColor"
                   aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else
                   class="block w-6 h-6"
                   xmlns="http://www.w3.org/2000/svg"
                   viewBox="0 0 24 24"
                   stroke="currentColor"
                   aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile User Menu (collapsible, appears above main mobile nav) -->
    <NavMenuTransition>
      <div v-show="mobileUserMenuOpen"
           class="overflow-hidden bg-neutral-50 sm:hidden dark:bg-neutral-700"
           id="mobile-user-menu"
           ref="mobileUserMenuRef">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <template v-for="userItem in userNavMenuItems" :key="'mobile-user-' + userItem.label">
            <Link v-if="userItem.href"
                  :href="userItem.href"
                  class="block px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
                  :class="[
                    isActive(userItem.routeName)
                      ? 'bg-neutral-500 dark:bg-neutral-600'
                      : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                  ]"
                  @click="closeMobileUserMenu()">
              {{ userItem.label }}
            </Link>
            <button v-if="userItem.command"
                    @click="() => { userItem.command && userItem.command(); closeMobileUserMenu(); }"
                    class="block px-3 py-2 w-full text-base font-medium !text-current rounded-md transition-colors duration-150 ease-in-out hover:bg-neutral-200 dark:hover:bg-neutral-700">
              {{ userItem.label }}
            </button>
          </template>
        </div>
      </div>
    </NavMenuTransition>

    <!-- Mobile Main Navigation (collapsible) -->
    <NavMenuTransition>
      <div v-show="mobileNavOpen"
           class="overflow-hidden sm:hidden bg-neutral-50 dark:bg-sub-panel-dark"
           id="mobile-main-menu"
           ref="mobileNavRef">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
            <!-- Regular Mobile Link -->
            <NavMenuItem :item="item" />

            <!-- Mobile Dropdown -->
            <NavMenuDropdown :item="item" />
          </template>
        </div>
      </div>
    </NavMenuTransition>
  </nav>
</template>
