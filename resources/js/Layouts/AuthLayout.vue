<script setup>
import Bugsnag from "@bugsnag/js";
import { usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";
import { useColorMode } from "@vueuse/core";

defineProps({
    title: {
        type: String,
        required: true,
    },
});

useColorMode();

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
</script>

<template>
<Head :title />
<div class="flex flex-col items-center pt-6 min-h-screen sm:justify-center sm:pt-0 bg-page dark:bg-page-dark">
  <div>
    <slot name="logo"/>
  </div>

  <div class="overflow-hidden px-6 py-4 mt-6 w-full shadow-md sm:max-w-md sm:rounded-lg bg-panel dark:bg-panel-dark">
    <slot/>
  </div>
</div>
</template>
