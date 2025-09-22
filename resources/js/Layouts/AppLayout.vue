<script setup lang="ts">
import Bugsnag from "@bugsnag/js";
import { Head, usePage } from "@inertiajs/vue3";
import { differenceInDays } from "date-fns";
import { computed, onMounted, provide, ref } from "vue";
import ObtrusiveNotification from "@/Components/ObtrusiveNotification.vue";
import { useDarkMode } from "@/Composables/useDarkMode.js";
import { useGlobalState } from "@/store";
import { EnableUserAvailability } from "@/Utils/provide-inject-keys.js"; // TODO AFTER REMOVING FLOATING-VUE, DELETE
import "@vuepic/vue-datepicker/dist/main.css"; // FIXME AFTER REMOVING VUE-DATEPICKER, DELETE
import "floating-vue/dist/style.css";

const { title } = defineProps<{
  title: string;
  fullWidth?: boolean;
}>();

const page = usePage();

const bugsnagKey = import.meta.env.VITE_BUGSNAG_FRONT_END_API_KEY;
onMounted(() => {
  if (bugsnagKey) {
    const user = page.props.auth.user;
    if (user?.id) {
      Bugsnag.setUser(user.id, user.email, user.name);
    }
  }
});

const { isDarkMode } = useDarkMode();

provide(EnableUserAvailability, !!page.props.enableUserAvailability || false);

const state = useGlobalState();
const showUpdateAvailabilityReminder = ref(false);

const didHideAvailabilityReminderOverOneDayAgo = computed(() => {
  const dismissedOn = state.value.dismissedAvailabilityOn;
  if (!dismissedOn) {
    return true;
  }
  return differenceInDays(new Date(), new Date(dismissedOn)) > 1;
});

const availabilityReminderPrompt = () => {
  if (page.props.user && page.props.needsToUpdateAvailability && didHideAvailabilityReminderOverOneDayAgo.value) {
    showUpdateAvailabilityReminder.value = true;
  }
};

onMounted(() => {
  availabilityReminderPrompt();
});
</script>

<template>
  <div class="text-neutral-900 dark:text-neutral-100 bg-gradient-to-b from-page  to-neutral-50 dark:bg-page-dark dark:bg-gradient-to-b dark:from-page-dark dark:to-neutral-950">
    <Head :title="title" />

    <div class="flex flex-col content-start min-h-dvh w-dvw max-w-full-dvw justify-stretch">
      <Nav class="border-b border-neutral-300 dark:border-neutral-700/85"
           @toggle-dark-mode="isDarkMode = $event" />

      <!-- Page Heading -->
      <header v-if="$slots.header"
              class="grid grid-cols-page px-4 xl:px-0 justify-center border-b border-neutral-200 w-dvw dark:text-gray-100 dark:border-b dark:border-neutral-700/85">
        <div class="py-6">
          <slot name="header" />
        </div>
      </header>

      <main class="flex flex-col flex-1 gap-0 justify-self-center main-content w-dvw grid-cols-page">
        <!-- Page Top -->
        <section v-if="$slots['page-top']" class="col-span-3 w-full dark:text-gray-100">
          <slot name="page-top" />
        </section>

        <!-- Page Content -->
        <section class="w-dvw grid grid-cols-page flex-1 justify-center items-start col-span-3 gap-0 sm:col-span-1 sm:col-start-2">
          <div class="grid grid-cols-1 justify-items-stretch justify-stretch pt-4 sm:pb-6 px-4 sm:px-4 bg-panel dark:bg-panel-dark overflow-hidden border border-t-0 std-border sm:rounded-b-md sm:mb-5">
            <slot />
          </div>
        </section>
      </main>

      <!-- Page Bottom -->
      <section v-if="$slots['page-bottom']" class="px-4 py-6 w-7xl sm:px-6 lg:px-8 dark:text-gray-100">
        <slot name="page-bottom" />
      </section>
    </div>
  </div>

  <PToast class="z-[9999]" position="top-center" group="default" :auto-z-index="false" />
  <PToast class="z-[9999]" position="center" group="center" :auto-z-index="false" />
  <PToast class="z-[9999]" position="bottom-center" group="bottom" :auto-z-index="false" />

  <ObtrusiveNotification full-screen-on-mobile v-model="showUpdateAvailabilityReminder" class="md:max-w-lg">
    <AvailabilityReminder @check-later="showUpdateAvailabilityReminder = false"/>
  </ObtrusiveNotification>
</template>

<!--suppress CssUnusedSymbol -->
<style>
/* TODO, DELETE AFTER REMOVING POPPER.JS */
.v-popper__popper .v-popper__wrapper {
    .v-popper__inner {
        @apply bg-white dark:bg-indigo-800 border border-white dark:border-indigo-800 shadow-lg text-slate-900 dark:text-slate-200 p-3;
    }
}

.v-popper__popper .v-popper__wrapper .v-popper__arrow-container {
    .v-popper__arrow-inner, .v-popper__arrow-outer {
        @apply border-white dark:border-indigo-800;
    }
}
</style>
