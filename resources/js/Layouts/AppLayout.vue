<script setup>
import Bugsnag from "@bugsnag/js";
import { router, usePage } from "@inertiajs/vue3";
import { differenceInDays } from "date-fns";
import { computed, inject, onMounted, provide, ref } from "vue";
import ObtrusiveNotification from "@/Components/ObtrusiveNotification.vue";
import useToast from "@/Composables/useToast";
import { useGlobalState } from "@/store";
import "@vuepic/vue-datepicker/dist/main.css"; // FIXME AFTER REMOVING VUE-DATEPICKER, DELETE
import "floating-vue/dist/style.css"; // TODO AFTER REMOVING FLOATING-VUE, DELETE

defineProps({
  title: String,
  user: Object,
  fullWidth: { type: Boolean, default: false },
});

const page = usePage();
const route = inject("route");

const bugsnagKey = import.meta.env.VITE_BUGSNAG_FRONT_END_API_KEY;
onMounted(() => {
  if (bugsnagKey) {
    const user = page.props.auth.user;
    if (user?.id) {
      Bugsnag.setUser(user.id, user.email, user.name);
    }
  }
});

const isDarkMode = ref(false);
provide("darkMode", isDarkMode);

provide("enableUserAvailability", !!page.props.enableUserAvailability || false);

const toast = useToast();

const flash = computed(() => page.props.jetstream.flash);
const state = useGlobalState();
const showUpdateAvailabilityReminder = ref(false);

const showToast = () => {
  if (!Array.isArray(flash.value) && flash.value.banner) {
    toast.message(flash.value.bannerStyle || "success", flash.value.banner);
  }
};

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
  showToast();
  availabilityReminderPrompt();
});

const checkAvailability = () => {
  checkLater();
  router.get(route("user.availability"));
};

const checkLater = () => {
  state.value.dismissedAvailabilityOn = new Date();
  showUpdateAvailabilityReminder.value = false;
};
</script>

<template>
  <div class="text-neutral-900 dark:text-neutral-100 bg-gradient-to-b from-page  to-neutral-50 dark:bg-page-dark dark:bg-gradient-to-b dark:from-page-dark dark:to-neutral-950">
    <Head :title="title" />

    <div class="flex flex-col content-start min-h-dvh w-dvw max-w-full-dvw justify-stretch">
      <Nav class="grid grid-cols-page justify-center px-4 xl:px-0  border-b border-neutral-300 dark:border-neutral-700/85"
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

  <PToast class="z-50" position="top-center" group="default" :auto-z-index="false" />
  <PToast class="z-50" position="center" group="center" :auto-z-index="false" />
  <PToast class="z-50" position="bottom-center" group="bottom" :auto-z-index="false" />

  <ObtrusiveNotification :draggable="false"
                         :close-on-escape="false"
                         :closable="false"
                         block-scroll
                         full-screen-on-mobile
                         v-model="showUpdateAvailabilityReminder"
                         class="md:max-w-lg">
    <div class="p-6 text-center dark:text-gray-100">
      <p>
        It seems like you haven't updated your availability in a while. Please make sure your availability is up
        to date.
      </p>
      <p class="my-3">Checking will hide this message for 1 month</p>
      <div class="flex flex-col justify-between w-full">
        <PButton class="justify-center mb-3 text-center" @click="checkAvailability">Check now</PButton>
        <PButton severity="secondary" outline class="justify-center text-center" @click="checkLater">
          I'll
          check later
        </PButton>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          You can always check your availability by going
          to the account menu item.
        </p>
      </div>
    </div>
  </ObtrusiveNotification>
</template>

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
