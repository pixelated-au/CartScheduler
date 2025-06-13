<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import headers from "./Lib/UserDataTableHeaders";
import DataTable from "@/Components/DataTable.vue";
import JetHelpText from "@/Jetstream/HelpText.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    users: Object,
    availableRoles: Array,
    permissions: Object,
});

const userSearch = ref("");

const onNewUser = () => {
    router.visit(route("admin.users.create"));
};

const onImportUsers = () => {
    router.visit(route("admin.users.import.show"));
};

const onDownloadUsers = async () => {
    window.location.href = route("admin.users-as-spreadsheet");
};

const handleSelection = (selection) => {
    router.visit(route("admin.users.edit", selection.id));
};
</script>

<template>
<AppLayout title="Users">
  <template #header>
    <div class="flex justify-between flex-wrap md:flex-nowrap">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight w-full md:w-auto">
        Users
      </h2>
      <div class="w-full md:w-auto hidden sm:flex gap-4">
        <PButton class=""
                 style-type="secondary"
                 outline
                 @click="onImportUsers">
          Import Users
        </PButton>
        <PButton outline severity="info" @click="onDownloadUsers">Download Users</PButton>
      </div>
    </div>
  </template>

  <div class="flex flex-col gap-4">
    <div class="flex items-end gap-24 py-3 sm:py-6 ">
      <div class="bg-panel flex-grow dark:bg-panel-dark sm:rounded-lg">
        <JetLabel for="search" value="Search for a user:"/>
        <!-- Overriding background colours for usability -->
        <JetInput id="search"
                  v-model="userSearch"
                  type="text"
                  class="mt-1 block w-full dark:bg-slate-700 sm:dark:bg-slate-800"/>
        <JetHelpText>Search on name, email, phone, role or any field</JetHelpText>
      </div>

      <div>
        <PButton style-type="primary" @click="onNewUser">
          New User
        </PButton>
        <JetHelpText>&nbsp;</JetHelpText>
      </div>
    </div>

    <div class="">
      <div class="bg-panel dark:bg-panel-dark">
        <data-table :headers="headers"
                    :items="users.data"
                    :search-value="userSearch"
                    @click-row="handleSelection">
          <template #item-email="{ email }">
            <a :href="`mailto:${email}`">{{ email }}</a>
          </template>

          <template #item-phone="{ mobile_phone }">
            <a :href="`tel:${mobile_phone}`">{{ mobile_phone }}</a>
          </template>

          <template #item-is_enabled="{ is_enabled }">
            <div v-if="is_enabled">Yes</div>
            <div v-else class="text-red-600 dark:text-red-400">No</div>
          </template>

          <template #item-is_unrestricted="{ is_unrestricted }">
            <div v-if="!is_unrestricted" class="text-red-600 dark:text-red-400">Yes</div>
            <div v-else>No</div>
          </template>
        </data-table>
      </div>
    </div>
  </div>
</AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
