import { usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

export type Item = {
  label: string;
  routeName?: string | undefined;
  href?: string;
  isDropdown?: boolean;
  submenu?: Item[];
  command?: () => void;
  hasUpdate?: boolean;
};

export default function() {
  const mobileNavOpen = ref(false);
  const mobileUserMenuOpen = ref(false);
  const desktopAdminDropdownOpen = ref(false);
  const desktopUserDropdownOpen = ref(false);
  const openMobileSubmenus = ref<Record<Item["label"], boolean>>({}); // E.g., { 'Administration': false }

  const page = usePage();
  const breakpoints = useBreakpoints(breakpointsTailwind);
  const isMobile = breakpoints.smaller("sm");

  watch(() => page.url, () => {
    mobileNavOpen.value = false;
    mobileUserMenuOpen.value = false;
    desktopAdminDropdownOpen.value = false;
    desktopUserDropdownOpen.value = false;
    Object.keys(openMobileSubmenus.value).forEach((key) => openMobileSubmenus.value[key] = false);
  });

  const closeMobileNav = (label?: Item["label"]) => {
    mobileNavOpen.value = false;

    if (label) {
      openMobileSubmenus.value[label] = false;
    }
  };

  const closeAllNav = () => {
    mobileNavOpen.value = false;
    mobileUserMenuOpen.value = false;
  };

  const toggleMobileNav = () => {
    mobileNavOpen.value = !mobileNavOpen.value;
    if (mobileNavOpen.value) mobileUserMenuOpen.value = false;
  };

  const toggleMobileSubmenu = (label: Item["label"]) => {
    openMobileSubmenus.value[label] = !openMobileSubmenus.value[label];
  };

  const toggleMobileUserMenu = () => {
    mobileUserMenuOpen.value = !mobileUserMenuOpen.value;
    if (mobileUserMenuOpen.value) mobileNavOpen.value = false;
  };

  const closeMobileUserMenu = () => {
    mobileUserMenuOpen.value = !mobileUserMenuOpen.value;
    if (mobileUserMenuOpen.value) mobileNavOpen.value = false;
  };

  const handleEscapeKey = (event: KeyboardEvent) => {
    if (event.key === "Escape") {
      if (isMobile.value) {
        if (mobileNavOpen.value) mobileNavOpen.value = false;
        if (mobileUserMenuOpen.value) mobileUserMenuOpen.value = false;
        // Consider closing active mobile submenu
        const openSub = Object.keys(openMobileSubmenus.value).find((key) => openMobileSubmenus.value[key]);
        if (openSub) openMobileSubmenus.value[openSub] = false;
      }
    }
  };

  const handleClickOutside = () => {
    if (desktopAdminDropdownOpen.value) {
      desktopAdminDropdownOpen.value = false;
    }
    if (desktopUserDropdownOpen.value) {
      desktopUserDropdownOpen.value = false;
    }
  };

  onMounted(() => {
    document.addEventListener("keydown", handleEscapeKey);
    document.addEventListener("click", handleClickOutside, true); // Capture phase for reliability
  });

  onUnmounted(() => {
    document.removeEventListener("keydown", handleEscapeKey);
    document.removeEventListener("click", handleClickOutside, true);
  });

  return {
    mobileNavOpen: computed(() => mobileNavOpen.value),
    mobileUserMenuOpen: computed(() => mobileUserMenuOpen.value),
    openMobileSubmenus,
    closeMobileUserMenu,
    closeMobileNav,
    closeAllNav,
    toggleMobileNav,
    toggleMobileSubmenu,
    toggleMobileUserMenu,
  };
}
