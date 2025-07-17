<script setup>
import { usePage } from "@inertiajs/vue3";
import { onMounted, inject } from "vue";
import JetSectionBorder from "@/Jetstream/SectionBorder.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ShowLocationAvailabilityForm from "@/Pages/Profile/Partials/ShowLocationAvailabilityForm.vue";
import ShowRegularAvailabilityForm from "@/Pages/Profile/Partials/ShowRegularAvailabilityForm.vue";
import ShowVacationsAvailabilityForm from "@/Pages/Profile/Partials/ShowVacationsAvailabilityForm.vue";

// TODO IS THIS COMPONENT IN USE??

defineProps({
  vacations: {
    type: Object,
    required: false,
  },
  availability: {
    type: Object,
    required: false,
  },
  selectedLocations: {
    type: Array,
    required: false,
  },
});

const route = inject("route");
const canChooseLocations = !!usePage().props.enableUserLocationChoices;

onMounted(() => {
  axios.post(route("set.viewed-availability"));
});
</script>

<template>
  <AppLayout title="Profile">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Profile
      </h2>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div>
          <ShowVacationsAvailabilityForm :vacations="vacations?.data" class="mt-10 sm:mt-0"/>

          <template v-if="canChooseLocations">
            <JetSectionBorder/>
            <ShowLocationAvailabilityForm :selected-locations="selectedLocations" class="mt-10 sm:mt-0"/>
          </template>

          <JetSectionBorder/>
          <ShowRegularAvailabilityForm :availability="availability?.data" class="mt-10 sm:mt-0"/>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
