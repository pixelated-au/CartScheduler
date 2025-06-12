<script setup>
import { onMounted, provide, ref } from "vue";
import useToast from "@/Composables/useToast.js";
import ReportForm from "@/Pages/Components/Dashboard/ReportForm.vue";

const show = defineModel({ required: true, type: Boolean });

const emit = defineEmits(["has-outstanding-reports"]);

const toast = useToast();

const outstandingReports = ref([]);
const tags = ref([]);
provide("report-tags", tags);

const getData = async () => {
    try {
        const [reportsResponse, tagsResponse] = await Promise.all([
            axios.get("/outstanding-reports"),
            axios.get("/get-report-tags"),
        ]);
        outstandingReports.value = reportsResponse.data;
        emit("has-outstanding-reports", outstandingReports.value.length);

        tags.value = tagsResponse.data.data;
    } catch (e) {
        toast.error(e.response.data.message);
    }
};

onMounted(() => {
    getData();
});

const reportSaved = () => getData();
</script>

<template>
<PDialog modal dismissable-mask v-model:visible="show">
  <template #header>
    <h3>Outstanding Reports</h3>
  </template>

  <div v-if="outstandingReports?.length" class="mt-5">
    <div v-for="report in outstandingReports"
         :key="`${report.shift_date}-${report.start_time}-${report.shift_id}-${report.user_id}`"
         class="pb-3 mb-20 border-b border-gray-200">
      <ReportForm :report="report" @saved="reportSaved()" />
    </div>
  </div>
  <div v-else>
    <h4 class="my-3 text-green-700 dark:text-green-400">No more reports outstanding 🎉</h4>
  </div>

  <template #footer>
    <PButton type="button" style-type="secondary" @click="show = false">Close</PButton>
  </template>
</PDialog>
</template>
