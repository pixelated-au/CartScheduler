<script setup lang="ts">
import axios, { isAxiosError } from "axios";
import { format, parse } from "date-fns";
import { computed, inject, nextTick, reactive, ref, useId, watch } from "vue";
import SubmitButton from "@/Components/Form/Buttons/SubmitButton.vue";
import useToast from "@/Composables/useToast.js";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import ReportTags from "@/Pages/Components/Dashboard/ReportTags.vue";
import { ReportTags as ReportTagsKey } from "@/Utils/provide-inject-keys";
import type { AxiosError } from "axios";
import type { ErrorBag, LaravelValidationResponse, TwentyFourHourTime } from "@/types/types";

// TODO How easy would it be be to convert component to an Inertia compatible component?
const props = defineProps<{
  report: App.Data.OutstandingReportsData;
}>();

const emit = defineEmits(["saved"]);

const tags = inject(ReportTagsKey);

const formData = reactive({
  shift_id: props.report.shift_id,
  shift_date: props.report.shift_date,
  start_time: props.report.start_time,
  shift_was_cancelled: props.report.shift_was_cancelled,
  placements_count: props.report.placements_count,
  videos_count: props.report.videos_count,
  requests_count: props.report.requests_count,
  comments: props.report.comments,
  tags: props.report.tags,
});

const errors = ref<ErrorBag>({});
const errorMessages = computed(() => {
  const messages: Record<keyof typeof errors.value, string> = {};
  for (const [key, value] of Object.entries(errors.value)) {
    messages[key] = value.join(", ");
  }
  return messages;
});
const hasErrors = computed(() => {
  if (isSaving.value) {
    return undefined;
  }
  return Object.keys(errorMessages.value).length > 0;
});
const wasSuccessful = ref(false);

const isSaving = ref(false);

let timeout = 0;
watch(hasErrors, (val) => {
  if (val) {
    wasSuccessful.value = false;
    return;
  }

  wasSuccessful.value = true;
  timeout = setTimeout(() => wasSuccessful.value = false, 1000);
});

const toast = useToast();

const saveReport = async () => {
  if (isSaving.value) return;
  if (timeout) clearTimeout(timeout);

  try {
    errors.value = {};
    isSaving.value = true;
    const response = await axios.post(route("save.report"), formData);
    toast.success(response.data.message, "Success");
    emit("saved");
  } catch (e) {
    if (!isAxiosError(e) || e.response?.status !== 422) {
      throw e;
    }

    if (e.status === 422) {
      errors.value = (e as AxiosError<LaravelValidationResponse>).response?.data.errors || {};
      toast.error(e.response?.data.message);
    } else {
      toast.error("An unexpected error occurred. This has been reported");
    }
  } finally {
    isSaving.value = false;
  }
};

const formatDate = (date: Date) => {
  return format(new Date(date), "E, do MMMM yyyy");
};

const maxCommentChars = 500;
const commentsRemainingCharacters = computed(() => {
  if (formData.comments) {
    return maxCommentChars - formData.comments.length;
  }
  return maxCommentChars;
});

watch(() => formData.comments, (value, oldValue) => {
  if (value && value.length > maxCommentChars) {
    nextTick(() => {
      formData.comments = oldValue;
    });
  }
});

const disableFields = computed(() => !!formData.shift_was_cancelled);

const formatTime = (time: TwentyFourHourTime) => format(parse(time, "HH:mm:ss", new Date()), "h:mm a");
const uId = useId();
</script>

<template>
  <h4><span class="mr-1">{{ formatDate(report.shift_date) }}:</span> {{ report.location_name }}</h4>
  <div>{{ formatTime(report.start_time) }} - {{ formatTime(report.end_time) }}</div>

  <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-2 gap-y-3">
    <div class="sm:col-span-2 flex items-center">
      <PCheckbox binary
                 :input-id="`${uId}-cancelled`"
                 v-model="formData.shift_was_cancelled"
                 :value="true"
                 class="mr-3" />
      <JetLabel :for="`${uId}-cancelled`" value="Shift was Canceled" />
    </div>

    <div v-if="tags?.length" class="sm:col-span-2">
      <div class="block font-medium mb-2">Quick Tags</div>
      <ReportTags @toggled="formData.tags = $event" />
      <div class="text-sm text-gray-500">Select any relevant tags</div>
    </div>

    <div class="col-span-2 sm:col-span-1">
      <JetLabel :for="`${uId}-placements`" value="Placements" :is-disabled="disableFields" />
      <PInputNumber :id="`${uId}-placements`"
                    v-model="formData.placements_count"
                    type="number"
                    class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :disabled="disableFields" />
      <JetInputError :message="errorMessages.placements_count" class="mt-2" />
    </div>
    <div class="col-span-2 sm:col-span-1">
      <JetLabel :for="`${uId}-videos`" value="Videos" :is-disabled="disableFields" />
      <PInputNumber :id="`${uId}-videos`"
                    v-model="formData.videos_count"
                    type="number"
                    class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :disabled="disableFields" />
      <JetInputError :message="errorMessages.videos_count" class="mt-2" />
    </div>
    <div class="col-span-2 sm:col-span-2">
      <JetLabel :for="`${uId}-requests`" value="Requests" :is-disabled="disableFields" />
      <PInputNumber :id="`${uId}-requests`"
                    v-model="formData.requests_count"
                    type="number"
                    class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :disabled="disableFields" />
      <div class="text-sm italic">Requests for studies, literature, follow-up, etc.</div>
      <JetInputError :message="errorMessages.requests_count" class="mt-2" />
    </div>
    <div class="sm:col-span-2 ">
      <JetLabel :for="`${uId}-comments`" value="Comments" />
      <PTextarea auto-resize
                 :id="`${uId}-comments`"
                 class="rounded-md shadow-sm w-full min-h-32"
                 v-model="formData.comments" />
      <div class="text-sm">{{ commentsRemainingCharacters }} characters remaining</div>
      <JetInputError :message="errorMessages.comments" class="mt-2" />
    </div>

    <div class="col-span-2 flex justify-center">
      <SubmitButton label="Save Report"
                    :processing="isSaving"
                    :success="wasSuccessful"
                    :failure="hasErrors"
                    :errors="errorMessages"
                    @click="saveReport" />
    </div>
  </div>
</template>
