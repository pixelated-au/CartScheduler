<script setup lang="ts">
import Bugsnag from "@bugsnag/js";
import { Head, usePage } from "@inertiajs/vue3";
import { useColorMode } from "@vueuse/core";
import { onMounted, ref } from "vue";
import ComponentSpinner from "@/Components/ComponentSpinner.vue";

interface PageProps {
  auth: {
    user: {
      id: string;
      email: string;
      name: string;
    };
  };

  [key: string]: unknown;
}

defineProps({
  title: {
    type: String,
    required: true,
  },
});

useColorMode();

const page = usePage<PageProps>();

const isProcessing = ref(false);
const showProcessing = ref(false);

const bugsnagKey = import.meta.env.VITE_BUGSNAG_FRONT_END_API_KEY;

onMounted(() => {
  if (bugsnagKey) {
    const user = page.props.auth.user;
    if (user?.id) {
      Bugsnag.setUser(user.id, user.email, user.name);
    }
  }
});

let timeoutId = 0;
const setProcessing = (value: boolean) => {
  isProcessing.value = value;

  if (value) {
    timeoutId = setTimeout(() => showProcessing.value = true, 1000);
    return;
  }

  showProcessing.value = false;
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
};
</script>

<template>
  <Head :title />
  <div class="flex flex-col items-center pt-6 min-h-screen sm:justify-center sm:pt-0 bg-page dark:bg-page-dark">
    <div>
      <slot name="logo"/>
    </div>

    <ComponentSpinner :show="showProcessing" class="w-full sm:max-w-md">
      <div class="overflow-hidden px-6 py-4 mt-6 shadow-md sm:rounded-lg bg-panel dark:bg-panel-dark">
        <slot :processing="isProcessing" :setProcessing="setProcessing"/>
      </div>
    </ComponentSpinner>
  </div>
</template>
