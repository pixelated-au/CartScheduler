<script setup>
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LocationForm from "@/Pages/Admin/Locations/Partials/LocationForm.vue";

defineProps({
  maxVolunteers: {
    type: Number,
  },
});

const listRouteAction = () => {
  router.visit(route("admin.locations.index"));
};
const newLocation = {
  data: {
    name: "",
    description: "",
    min_volunteers: 2,
    max_volunteers: 3,
    requires_brother: true,
    is_enabled: true,
    shifts: [],
  },
};
</script>

<template>
  <AppLayout title="Create a new Location">
    <template #header>
      <div class="flex justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Location</h2>
        <PButton class="mx-3" type="button" severity="secondary" outline @click.prevent="listRouteAction">
          Back
        </PButton>
      </div>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <LocationForm :location="newLocation" action="add" :max-volunteers="maxVolunteers"/>
      </div>
    </div>
  </AppLayout>
</template>
