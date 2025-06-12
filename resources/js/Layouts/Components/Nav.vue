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

console.log(route().current());

// Helper to check if a route is active
const isActive = (routeName) => route().current() === routeName;
</script>

<template>
<nav class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-md relative z-50">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo Placeholder & Mobile User Menu Toggle (far left on mobile) -->
      <div class="flex items-center">
        <Link :href="route('dashboard')" class="flex-shrink-0">
          <!-- Placeholder for Logo -->
          <JetApplicationMark class="block h-9 w-auto" />
          <!-- Or a simple text/SVG logo -->
          <!-- <span class="font-semibold text-xl">MyApp</span> -->
        </Link>

        <!-- Mobile User Menu Toggle (becomes visible on mobile, order controlled by flex) -->
        <div class="sm:hidden ml-auto mr-3">
          <button @click="toggleMobileUserMenu"
                  type="button"
                  class="p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white transition"
                  aria-controls="mobile-user-menu"
                  :aria-expanded="mobileUserMenuOpen.toString()">
            <span class="sr-only">Open user menu</span>
            <!-- User Icon (e.g., Heroicons user-circle) -->
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Desktop Main Navigation -->
      <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-4">
        <template v-for="item in mainMenuItems" :key="item.label">
          <!-- Regular Link -->
          <Link v-if="!item.isDropdown"
                :href="item.href"
                class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150 ease-in-out"
                :class="[
                  isActive(item.routeName)
                    ? 'bg-primary-500 text-white dark:bg-primary-600'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                ]"
                :aria-current="isActive(item.routeName) ? 'page' : undefined">
            {{ item.label }}
          </Link>

          <!-- Administration Dropdown -->
          <div v-if="item.isDropdown && item.label === 'Administration'" class="relative" ref="adminDropdownRef">
            <button @click="toggleDesktopAdminDropdown"
                    type="button"
                    class="px-3 py-2 rounded-md text-sm font-medium flex items-center transition-colors duration-150 ease-in-out"
                    :class="[
                      desktopAdminDropdownOpen || item.submenu.some(subItem => isActive(subItem.routeName))
                        ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                    ]"
                    aria-haspopup="true"
                    :aria-expanded="desktopAdminDropdownOpen.toString()"
                    aria-controls="desktop-admin-menu">
              <span>{{ item.label }}</span>
              <svg class="ml-1 h-5 w-5 transform transition-transform duration-150"
                   :class="{ 'rotate-180': desktopAdminDropdownOpen }"
                   xmlns="http://www.w3.org/2000/svg"
                   viewBox="0 0 20 20"
                   fill="currentColor"
                   aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
              <div v-show="desktopAdminDropdownOpen"
                   id="desktop-admin-menu"
                   class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                   role="menu"
                   aria-orientation="vertical"
                   :aria-labelledby="item.label + '-button'">
                <Link v-for="subItem in item.submenu"
                      :key="subItem.label"
                      :href="subItem.href"
                      class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 w-full text-left transition-colors duration-150 ease-in-out"
                      :class="{ 'bg-gray-100 dark:bg-gray-600 font-semibold': isActive(subItem.routeName) }"
                      role="menuitem"
                      @click="desktopAdminDropdownOpen = false">
                  {{ subItem.label }}
                </Link>
              </div>
            </transition>
          </div>
        </template>
      </div>

      <!-- Desktop User Menu (far right) -->
      <div class="hidden sm:ml-6 sm:flex sm:items-center" ref="userDropdownRef">
        <div class="relative">
          <button @click="toggleDesktopUserDropdown"
                  type="button"
                  class="bg-gray-100 dark:bg-gray-800 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-800 focus:ring-primary-500 transition"
                  id="user-menu-button"
                  aria-haspopup="true"
                  :aria-expanded="desktopUserDropdownOpen.toString()">
            <span class="sr-only">Open user menu</span>
            <!-- User Avatar Placeholder -->
            <img v-if="page.props.auth.user?.profile_photo_url" class="h-8 w-8 rounded-full" :src="page.props.auth.user.profile_photo_url" :alt="page.props.auth.user?.name || 'User Avatar'">
            <span v-else class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600">
              <span class="text-sm font-medium leading-none text-gray-700 dark:text-gray-200">{{ page.props.auth.user?.name?.charAt(0) || 'U' }}</span>
            </span>
          </button>
          <transition enter-active-class="transition ease-out duration-100"
                      enter-from-class="transform opacity-0 scale-95"
                      enter-to-class="transform opacity-100 scale-100"
                      leave-active-class="transition ease-in duration-75"
                      leave-from-class="transform opacity-100 scale-100"
                      leave-to-class="transform opacity-0 scale-95">
            <div v-show="desktopUserDropdownOpen"
                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                 role="menu"
                 aria-orientation="vertical"
                 aria-labelledby="user-menu-button">
              <template v-for="userItem in userNavMenuItems" :key="userItem.label">
                <Link v-if="userItem.href"
                      :href="userItem.href"
                      class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 w-full text-left transition-colors duration-150 ease-in-out"
                      :class="{ 'bg-gray-100 dark:bg-gray-600 font-semibold': isActive(userItem.routeName) }"
                      role="menuitem"
                      @click="desktopUserDropdownOpen = false">
                  {{ userItem.label }}
                </Link>
                <button v-if="userItem.command"
                        @click="() => { userItem.command(); desktopUserDropdownOpen = false; }"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-150 ease-in-out"
                        role="menuitem">
                  {{ userItem.label }}
                </button>
              </template>
            </div>
          </transition>
        </div>
      </div>

      <!-- Mobile Main Menu Toggle (Hamburger) -->
      <div class="-mr-2 flex items-center sm:hidden">
        <button @click="toggleMobileNav"
                type="button"
                class="p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white transition"
                aria-controls="mobile-main-menu"
                :aria-expanded="mobileNavOpen.toString()">
          <span class="sr-only">Open main menu</span>
          <svg v-if="!mobileNavOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
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
    <div v-show="mobileUserMenuOpen" class="sm:hidden bg-gray-50 dark:bg-gray-750 overflow-hidden" id="mobile-user-menu" ref="mobileUserMenuRef">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <template v-for="userItem in userNavMenuItems" :key="'mobile-user-' + userItem.label">
          <Link v-if="userItem.href"
                :href="userItem.href"
                class="block px-3 py-2 rounded-md text-base font-medium transition-colors duration-150 ease-in-out"
                :class="[
                  isActive(userItem.routeName)
                    ? 'bg-primary-500 text-white dark:bg-primary-600'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                ]"
                @click="mobileUserMenuOpen = false">
            {{ userItem.label }}
          </Link>
          <button v-if="userItem.command"
                  @click="() => { userItem.command(); mobileUserMenuOpen = false; }"
                  class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150 ease-in-out">
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
    <div v-show="mobileNavOpen" class="sm:hidden bg-gray-50 dark:bg-gray-750 overflow-hidden" id="mobile-main-menu" ref="mobileNavRef">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <template v-for="item in mainMenuItems" :key="'mobile-main-' + item.label">
          <!-- Regular Mobile Link -->
          <Link v-if="!item.isDropdown"
                :href="item.href"
                class="block px-3 py-2 rounded-md text-base font-medium transition-colors duration-150 ease-in-out"
                :class="[
                  isActive(item.routeName)
                    ? 'bg-primary-500 text-white dark:bg-primary-600'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                ]"
                @click="mobileNavOpen = false">
            {{ item.label }}
          </Link>

          <!-- Mobile Dropdown (Administration) -->
          <div v-if="item.isDropdown && item.label === 'Administration'">
            <button @click="toggleMobileSubmenu(item.label)"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150 ease-in-out"
                    :aria-expanded="(openMobileSubmenus[item.label] || false).toString()"
                    :aria-controls="'mobile-submenu-' + item.label">
              <span>{{ item.label }}</span>
              <svg class="ml-1 h-5 w-5 transform transition-transform duration-150"
                   :class="{ 'rotate-180': openMobileSubmenus[item.label] }"
                   xmlns="http://www.w3.org/2000/svg"
                   viewBox="0 0 20 20"
                   fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <transition @before-enter="onBeforeEnter"
                        @enter="onEnter"
                        @after-enter="onAfterEnter"
                        @before-leave="onBeforeLeave"
                        @leave="onLeave"
                        @after-leave="onAfterLeave">
              <div v-show="openMobileSubmenus[item.label]" :id="'mobile-submenu-' + item.label" class="pl-4 mt-1 space-y-1 overflow-hidden">
                <Link v-for="subItem in item.submenu"
                      :key="'mobile-sub-' + subItem.label"
                      :href="subItem.href"
                      class="block px-3 py-2 rounded-md text-base font-medium transition-colors duration-150 ease-in-out"
                      :class="[
                        isActive(subItem.routeName)
                          ? 'bg-primary-500 text-white dark:bg-primary-600'
                          : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                      ]"
                      @click="() => { mobileNavOpen = false; openMobileSubmenus[item.label] = false; }">
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
