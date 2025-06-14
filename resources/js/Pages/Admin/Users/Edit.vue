<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { inject } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import UserProfileForm from "@/Pages/Admin/Users/Partials/UserProfileForm.vue";
import ShowLocationAvailabilityForm from "@/Pages/Profile/Partials/ShowLocationAvailabilityForm.vue";
import ShowRegularAvailabilityForm from "@/Pages/Profile/Partials/ShowRegularAvailabilityForm.vue";
import ShowVacationsAvailabilityForm from "@/Pages/Profile/Partials/ShowVacationsAvailabilityForm.vue";

defineProps({
    editUser: Object,
    availableRoles: Array,
    permissions: Object,
});

const route = inject("route");

const listRouteAction = () => {
    router.visit(route("admin.users.index"));
};

const canChooseLocations = !!usePage().props.enableUserLocationChoices;
</script>

<template>
<AppLayout :title="`User: ${editUser.data.name}`">
  <template #header>
    <div class="flex justify-between">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        User {{ editUser.data.name }}
      </h2>
      <PButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
        Back
      </PButton>
    </div>
  </template>

  <div>
    <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
      <UserProfileForm :user="editUser.data" />
    </div>

    <template v-if="$page.props.enableUserAvailability">
      <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
        <ShowVacationsAvailabilityForm :userId="editUser.data.id"
                                       :vacations="editUser.data.vacations"
                                       class="mt-10 sm:mt-0" />
      </div>
      <div v-if="canChooseLocations" class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
        <ShowLocationAvailabilityForm :userId="editUser.data.id"
                                      :selected-locations="editUser.data.selectedLocations"
                                      class="mt-10 sm:mt-0" />
      </div>
      <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
        <ShowRegularAvailabilityForm :userId="editUser.data.id"
                                     :availability="editUser.data.availability"
                                     class="mt-10 sm:mt-0" />
      </div>
    </template>
  </div>
</AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
