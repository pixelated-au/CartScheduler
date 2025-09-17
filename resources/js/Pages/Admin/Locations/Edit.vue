<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LocationForm from "@/Pages/Admin/Locations/Partials/LocationForm.vue";

defineProps<{
  location: App.Data.LocationAdminData;
  maxVolunteers: number;
}>();

const listRouteAction = () => {
  router.visit(route("admin.locations.index"));
};
</script>

<template>
  <AppLayout :title="`Location: ${location.name}`">
    <template #header>
      <div class="flex justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Location {{ location.name }}
        </h2>
        <PButton class="mx-3" type="button" severity="secondary" outline @click.prevent="listRouteAction">
          Back
        </PButton>
      </div>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <LocationForm :location="location" :max-volunteers="maxVolunteers" action="edit"/>
      </div>
    </div>
  </AppLayout>
</template>
