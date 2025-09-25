<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { onMounted, onUnmounted, ref } from "vue";
import JetApplicationMark from "@/Components/CSLogo.vue";
import NavDesktopMain from "@/Layouts/Components/NavDesktopMain.vue";
import useNavEvents from "./Composables/useNavEvents";

const breakpoints = useBreakpoints(breakpointsTailwind);
const isNotMobile = breakpoints.greaterOrEqual("sm");

const { toggleMobileNav, closeMobileNav, addEscapeHandler, removeEscapeHandler } = useNavEvents();

const mobileNavIsShowing = ref(false);

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

      <!-- Desktop Menu -->
      <ul class="hidden sm:flex items-center grow sm:ml-6 sm:space-x-4">
        <NavDesktopMain/>
      </ul>

      <DarkMode v-show="isNotMobile || mobileNavIsShowing" class="me-2" />

      <!-- Mobile Main Menu Toggle (Hamburger) -->
      <div class="flex items-center sm:hidden">
        <NavHamburgerButton :is-active="mobileNavIsShowing"
                            aria-controls="mobile-main-menu"
                            aria-label="Mobile device navigation menu"
                            :aria-expanded="mobileNavIsShowing ? 'true' : 'false'"
                            @click="toggleMobileNav" />
      </div>

      <!-- Desktop User Menu -->
      <ul class="hidden sm:flex sm:items-center justify-end items-center sm:me-4">
        <NavCurrentUser/>
      </ul>
    </div>

    <!-- Mobile Main Navigation (collapsible) -->
    <NavMobile id="mobile-main-menu"/>
  </nav>
</template>
