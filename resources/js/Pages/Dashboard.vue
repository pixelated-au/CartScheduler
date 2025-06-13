<script setup>
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import CartReservation from "@/Pages/Components/Dashboard/CartReservation.vue";
import ReportsModal from "@/Pages/Components/Dashboard/ReportsModal.vue";

defineProps({
    user: Object,
});

const outstandingReportCount = ref(0);

const reportsLabel = computed(() =>
    outstandingReportCount.value === 1 ? "Report" : "Reports");

const showReportsModal = ref(false);
</script>

<template>
<AppLayout title="Dashboard">
  <template #header>
    <div class="flex flex-col justify-between w-full sm:flex-row">
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Dashboard</h2>
      <div>
        <PButton v-if="outstandingReportCount"
                 icon="iconify mdi--bell"
                 :label="`${ outstandingReportCount } ${ reportsLabel } Outstanding`"
                 type="button"
                 severity="warn"
                 class="mt-3 w-full font-bold sm:mt-0 sm:w-auto"
                 @click="showReportsModal = true"/>
      </div>
    </div>
  </template>

  <div class="py-2 dashboard">
    <div class="mx-auto max-w-7xl">
      <div class="overflow-hidden bg-panel dark:bg-panel-dark sm:rounded-lg">
        <CartReservation :user="user"/>
      </div>
    </div>
  </div>
</AppLayout>
<ReportsModal v-model="showReportsModal"
              @has-outstanding-reports="outstandingReportCount = $event"/>
</template>
