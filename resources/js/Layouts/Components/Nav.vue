<script setup>
import { computed, inject, ref, onBeforeMount, onMounted, onUnmounted, nextTick, watch, useTemplateRef } from "vue";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { Link, router, usePage } from "@inertiajs/vue3";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue"; // Assuming this is your logo component

defineEmits(["toggle-dark-mode"]);

const page = usePage();
const breakpoints = useBreakpoints(breakpointsTailwind);
const route = inject("route");

let permissions;
onBeforeMount(() => {
    permissions = page.props.pagePermissions;
});

const isMobile = breakpoints.smaller("sm");

// --- Menu States ---
const mobileNavOpen = ref(false);
const mobileUserMenuOpen = ref(false);
const desktopAdminDropdownOpen = ref(false);
const desktopUserDropdownOpen = ref(false);
const openMobileSubmenus = ref({}); // E.g., { 'Administration': false }

// --- Refs for click outside ---
const adminDropdownRef = useTemplateRef("adminDropdownRef");
const userDropdownRef = useTemplateRef("userDropdownRef");
const mobileNavRef = useTemplateRef("mobileNavRef");
const mobileUserMenuRef = useTemplateRef("mobileUserMenuRef");

// --- Computed Menu Items (adapted from original) ---
const hasUpdate = computed(() => page.props.hasUpdate); // Keep if used for update indicators

const mainMenuItems = computed(() => {
    if (!permissions) return [];
    const items = [{ label: "Dashboard", routeName: "dashboard", href: route("dashboard") }];

    if (permissions.canAdmin) {
        const adminSubmenu = [
            { label: "Admin Dashboard", routeName: "admin.dashboard", href: route("admin.dashboard") },
            { label: "Users", routeName: "admin.users.index", href: route("admin.users.index") },
            { label: "Locations", routeName: "admin.locations.index", href: route("admin.locations.index") },
            { label: "Reports", routeName: "admin.reports.index", href: route("admin.reports.index") },
        ];
        if (permissions.canEditSettings) {
            adminSubmenu.push({ label: "Settings", routeName: "admin.settings", href: route("admin.settings") /* , hasUpdate: hasUpdate.value */ });
        }
        items.push({
            label: "Administration",
            // hasUpdate: hasUpdate.value, // Example if an entire section can have an update
            isDropdown: true,
            submenu: adminSubmenu,
        });
    }
    return items;
});

const userNavMenuItems = computed(() => {
    if (!page.props.auth || !page.props.auth.user) return [];
    const items = [
        { label: "Profile", routeName: "profile.show", href: route("profile.show") },
    ];
    if (page.props.enableUserAvailability) {
        items.push({ label: "Availability", routeName: "user.availability", href: route("user.availability") });
    }
    items.push({ label: "Log Out", command: () => logout() });
    return items;
});

const logout = () => {
    router.post(route("logout"), {}, {
        onFinish: () => {
            mobileNavOpen.value = false;
            mobileUserMenuOpen.value = false;
            desktopAdminDropdownOpen.value = false;
            desktopUserDropdownOpen.value = false;
        },
    });
};

// --- Toggle Functions ---
const toggleMobileNav = () => {
    mobileNavOpen.value = !mobileNavOpen.value;
    if (mobileNavOpen.value) mobileUserMenuOpen.value = false;
};
const toggleMobileUserMenu = () => {
    mobileUserMenuOpen.value = !mobileUserMenuOpen.value;
    if (mobileUserMenuOpen.value) mobileNavOpen.value = false;
};

const toggleDesktopAdminDropdown = () => {
    desktopAdminDropdownOpen.value = !desktopAdminDropdownOpen.value;
    if (desktopAdminDropdownOpen.value) desktopUserDropdownOpen.value = false;
};
const toggleDesktopUserDropdown = () => {
    desktopUserDropdownOpen.value = !desktopUserDropdownOpen.value;
    if (desktopUserDropdownOpen.value) desktopAdminDropdownOpen.value = false;
};

const toggleMobileSubmenu = (label) => {
    openMobileSubmenus.value[label] = !openMobileSubmenus.value[label];
};

// Close all menus when route changes
watch(() => page.url, () => {
    mobileNavOpen.value = false;
    mobileUserMenuOpen.value = false;
    desktopAdminDropdownOpen.value = false;
    desktopUserDropdownOpen.value = false;
    Object.keys(openMobileSubmenus.value).forEach((key) => openMobileSubmenus.value[key] = false);
});

// --- Animation Hooks (Tailwind doesn't need JS for simple opacity/transform, but height needs it) ---
const onBeforeEnter = (el) => {
    el.style.opacity = "0";
    el.style.maxHeight = "0px";
};
const onEnter = (el, done) => {
    nextTick(() => {
        el.style.transitionProperty = "max-height, opacity";
        el.style.transitionDuration = "300ms";
        el.style.transitionTimingFunction = "ease-out";
        el.style.opacity = "1";
        el.style.maxHeight = el.scrollHeight + "px";
    });
    // Call done when transition finishes
    el.addEventListener("transitionend", done, { once: true });
};
const onAfterEnter = (el) => {
    el.style.maxHeight = ""; // Use 'auto' or remove for content to dictate height
    el.style.transitionProperty = "";
    el.style.transitionDuration = "";
    el.style.transitionTimingFunction = "";
};
const onBeforeLeave = (el) => {
    el.style.transitionProperty = "max-height, opacity";
    el.style.transitionDuration = "300ms";
    el.style.transitionTimingFunction = "ease-in";
    el.style.maxHeight = el.scrollHeight + "px";
    el.style.opacity = "1"; // Start fully visible
};
const onLeave = (el, done) => {
    nextTick(() => {
        el.style.maxHeight = "0px";
        el.style.opacity = "0";
    });
    el.addEventListener("transitionend", done, { once: true });
};
const onAfterLeave = (el) => {
    el.style.maxHeight = "";
    el.style.opacity = "";
    el.style.transitionProperty = "";
    el.style.transitionDuration = "";
    el.style.transitionTimingFunction = "";
};

// --- Event Handlers for Closing Menus ---
const handleEscapeKey = (event) => {
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

const handleClickOutside = (event) => {
    // Desktop dropdowns
    if (desktopAdminDropdownOpen.value && adminDropdownRef.value && !adminDropdownRef.value.contains(event.target)) {
        desktopAdminDropdownOpen.value = false;
    }
    if (desktopUserDropdownOpen.value && userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
        desktopUserDropdownOpen.value = false;
    }
    // Mobile menus (if they are overlays)
    // This example assumes they are part of the flow, so outside click might not be desired for main mobile nav
    // but could be for user menu if it overlays other content.
    // For simplicity, Escape key is the primary mobile close mechanism here.
};

onMounted(() => {
    document.addEventListener("keydown", handleEscapeKey);
    document.addEventListener("click", handleClickOutside, true); // Capture phase for reliability
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleEscapeKey);
    document.removeEventListener("click", handleClickOutside, true);
});

// Helper to check if a route is active
const isActive = (routeName) => route().current() === routeName;
</script>

<template>
<nav class="relative z-50 bg-panel dark:bg-panel-dark">
  <div class="container">
    <div class="flex justify-between items-center h-16">
      <!-- Left side: Logo and Desktop Main Menu -->
      <div class="flex items-center">
        <Link :href="route('dashboard')" class="flex-shrink-0">
          <JetApplicationMark class="block w-auto h-9" />
        </Link>

        <!-- Desktop Main Menu -->
        <div class="hidden sm:flex sm:ml-6 sm:space-x-4">
          <template v-for="item in mainMenuItems" :key="item.label">
            <!-- Regular Link -->
            <Link v-if="!item.isDropdown"
                  :href="item.href"
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
                      class="flex items-center px-3 py-2 rounded-md font-medium transition-colors duration-150 ease-in-out text-current hover:underline decoration-dotted"
                      :class="[
                        desktopAdminDropdownOpen || item.submenu.some(subItem => isActive(subItem.routeName))
                          ? '!font-bold underline underline-offset-4 decoration-dotted'
                          : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                      ]"
                      aria-haspopup="true"
                      :aria-expanded="desktopAdminDropdownOpen.toString()"
                      aria-controls="desktop-admin-menu">
                <span>{{ item.label }}</span>
                <span class="iconify mdi--chevron-down text-lg transition-rotate duration-500 delay-100 ease-in-out"
                      :class="{ 'rotate-180 !delay-0 !duration-300': desktopAdminDropdownOpen }"></span>
              </button>
              <transition @before-enter="onBeforeEnter"
                          @enter="onEnter"
                          @after-enter="onAfterEnter"
                          @before-leave="onBeforeLeave"
                          @leave="onLeave"
                          @after-leave="onAfterLeave">
                <div v-show="desktopAdminDropdownOpen"
                     id="desktop-admin-menu"
                     class="overflow-hidden absolute right-0 z-50 py-1 mt-2 w-48 bg-white rounded-md ring-1 ring-black ring-opacity-5 shadow-lg origin-top-right dark:bg-neutral-700 focus:outline-none"
                     role="menu"
                     aria-orientation="vertical"
                     :aria-labelledby="item.label + '-button'">
                  <Link v-for="subItem in item.submenu"
                        :key="subItem.label"
                        :href="subItem.href"
                        class="flex items-center px-6 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600"
                        :class="{ '!no-underline': isActive(subItem.routeName) }"
                        role="menuitem"
                        @click="desktopAdminDropdownOpen = false">
                    <span v-if="isActive(subItem.routeName)" class="iconify mdi--chevron-right -ml-4"></span>
                    <span :class="{ '!font-bold underline underline-offset-4 decoration-dotted': isActive(subItem.routeName) }">
                      {{ subItem.label }}
                    </span>
                  </Link>
                </div>
              </transition>
            </div>
          </template>
        </div>
      </div>

      <!-- Right side: Mobile Toggles & Desktop User Menu -->
      <div class="flex justify-end items-center">
        <DarkMode @is-dark-mode="$emit('toggle-dark-mode', $event)" />

        <!-- Mobile User Menu Toggle -->
        <div class="sm:hidden mr-3">
          <button @click="toggleMobileUserMenu"
                  type="button"
                  class="p-2 rounded-md transition hover:text-white hover:bg-neutral-700"
                  aria-controls="mobile-user-menu"
                  :aria-expanded="mobileUserMenuOpen.toString()">
            <span class="sr-only">Open user menu</span>
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                    :aria-expanded="desktopUserDropdownOpen.toString()">
              <span class="sr-only">Open user menu</span>
              <img v-if="page.props.auth.user?.profile_photo_url" class="w-8 h-8 rounded-full" :src="page.props.auth.user.profile_photo_url" :alt="page.props.auth.user?.name || 'User Avatar'">
              <span v-else class="inline-flex justify-center items-center w-8 h-8 bg-neutral-300 rounded-full dark:bg-neutral-600">
                <span class="font-medium leading-none !text-current">{{ page.props.auth.user?.name?.charAt(0) || 'U' }}</span>
              </span>
            </button>
            <transition @before-enter="onBeforeEnter"
                        @enter="onEnter"
                        @after-enter="onAfterEnter"
                        @before-leave="onBeforeLeave"
                        @leave="onLeave"
                        @after-leave="onAfterLeave">
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
                  <button v-if="userItem.command"
                          @click="() => { userItem.command(); desktopUserDropdownOpen = false; }"
                          class="block px-4 py-2 w-full !text-current transition-colors duration-150 ease-in-out hover:bg-neutral-100 dark:hover:bg-neutral-600"
                          role="menuitem">
                    {{ userItem.label }}
                  </button>
                </template>
              </div>
            </transition>
          </div>
        </div>

        <!-- Mobile Main Menu Toggle (Hamburger) -->
        <div class="flex items-center -mr-2 sm:hidden">
          <button @click="toggleMobileNav"
                  type="button"
                  class="p-2 rounded-md transition hover:text-white hover:bg-neutral-700"
                  aria-controls="mobile-main-menu"
                  :aria-expanded="mobileNavOpen.toString()">
            <span class="sr-only">Open main menu</span>
            <svg v-if="!mobileNavOpen" class="block w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="block w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile User Menu (collapsible, appears above main mobile nav) -->
  <transition @before-enter="onBeforeEnter"
              @enter="onEnter"
              @after-enter="onAfterEnter"
              @before-leave="onBeforeLeave"
              @leave="onLeave"
              @after-leave="onAfterLeave">
    <div v-show="mobileUserMenuOpen" class="overflow-hidden bg-neutral-50 sm:hidden dark:bg-neutral-700" id="mobile-user-menu" ref="mobileUserMenuRef">
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
                @click="mobileUserMenuOpen = false">
            {{ userItem.label }}
          </Link>
          <button v-if="userItem.command"
                  @click="() => { userItem.command(); mobileUserMenuOpen = false; }"
                  class="block px-3 py-2 w-full text-base font-medium !text-current rounded-md transition-colors duration-150 ease-in-out hover:bg-neutral-200 dark:hover:bg-neutral-700">
            {{ userItem.label }}
          </button>
        </template>
      </div>
    </div>
  </transition>

  <!-- Mobile Main Navigation (collapsible) -->
  <transition @before-enter="onBeforeEnter"
              @enter="onEnter"
              @after-enter="onAfterEnter"
              @before-leave="onBeforeLeave"
              @leave="onLeave"
              @after-leave="onAfterLeave">
    <div v-show="mobileNavOpen" class="overflow-hidden sm:hidden bg-neutral-50 dark:bg-sub-panel-dark" id="mobile-main-menu" ref="mobileNavRef">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
          <!-- Regular Mobile Link -->
          <Link v-if="!item.isDropdown"
                :href="item.href"
                class="block px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
                :class="[
                  isActive(item.routeName)
                    ? '!font-bold underline underline-offset-4 decoration-dashed'
                    : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                ]"
                @click="mobileNavOpen = false">
            <span v-if="isActive(item.routeName)" class="iconify mdi--chevron-right -ml-4"></span>
            {{ item.label }}
          </Link>

          <!-- Mobile Dropdown (Administration) -->
          <div v-if="item.isDropdown && item.label === 'Administration'">
            <button @click="toggleMobileSubmenu(item.label)"
                    type="button"
                    class="flex justify-between items-center px-3 py-2 w-full font-medium rounded-md transition-colors duration-150 ease-in-out hover:ring-1 ring-black/25 dark:ring-white/25"
                    :aria-expanded="(openMobileSubmenus[item.label] || false).toString()"
                    :aria-controls="'mobile-submenu-' + item.label">
              <span>{{ item.label }}</span>
              <span class="iconify mdi--chevron-down text-2xl transition-rotate duration-500 delay-100 ease-in-out"
                    :class="{ 'rotate-180': openMobileSubmenus[item.label] }"></span>
            </button>
            <transition @before-enter="onBeforeEnter"
                        @enter="onEnter"
                        @after-enter="onAfterEnter"
                        @before-leave="onBeforeLeave"
                        @leave="onLeave"
                        @after-leave="onAfterLeave">
              <div v-show="openMobileSubmenus[item.label]" :id="'mobile-submenu-' + item.label" class="overflow-hidden mt-3 pl-4 mt-1 gap-2">
                <Link v-for="subItem in item.submenu"
                      :key="'mobile-sub-' + subItem.label"
                      :href="subItem.href"
                      class="flex items-center px-3 py-2 text-base font-medium rounded-md transition-colors duration-150 ease-in-out !text-current"
                      :class="[
                        isActive(subItem.routeName)
                          ? '!font-bold underline underline-offset-4 decoration-dashed'
                          : 'dark: hover:bg-neutral-200 dark:hover:bg-neutral-700'
                      ]"
                      @click="() => { mobileNavOpen = false; openMobileSubmenus[item.label] = false; }">
                  <span v-if="isActive(subItem.routeName)" class="iconify mdi--chevron-right -ml-4"></span>
                  {{ subItem.label }}
                </Link>
              </div>
            </transition>
          </div>
        </template>
      </div>
    </div>
  </transition>
</nav>
</template>
