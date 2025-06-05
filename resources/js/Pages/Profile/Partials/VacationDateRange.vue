<script setup>
import { parseISO, addDays } from "date-fns";
import formatISO from "date-fns/formatISO";
import { computed, watch, ref } from "vue";
import JetInputError from "@/Jetstream/InputError.vue";

const props = defineProps({
  startDate: {
    type: String,
  },
  endDate: {
    type: String,
  },
  startError: {
    type: String,
    required: false,
  },
  endError: {
    type: String,
    required: false,
  },
});

const emit = defineEmits([
  "update:startDate",
  "update:endDate",
]);

const formatForIso = (date) => formatISO(date, { representation: "date" });

const fieldFormat = "D d M yy";

const start = ref();
const end = ref();

watch(() => props.startDate, (value) => {
  if (value) {
    start.value = parseISO(value);
  }
}, { immediate: true });

watch(() => props.endDate, (value) => {
  if (value) {
    end.value = parseISO(value);
  }
}, { immediate: true });

watch(start, (value) => {
  if (value) {
    emit("update:startDate", formatForIso(value));
  }
});

watch(end, (value) => {
  if (value) {
    emit("update:endDate", formatForIso(value));
  }
});

const minDate = computed(() => start.value
  ? addDays(start.value, 1)
  : addDays(new Date(), 1));

const maxDate = computed(() => end.value
  ? end.value
  : undefined);
</script>

<template>
  <div class="grid grid-cols-[auto_minmax(0,_1fr)] gap-2 items-center sm:flex sm:space-x-4">
    <label class="contents">
      <span class="font-bold flex-grow-0">From:</span>

      <PDatePicker
          v-model="start"
          :minDate="new Date()"
          :maxDate
          :dateFormat="fieldFormat" />
      <JetInputError :message="startError" />
    </label>

    <label class="contents">
      <span class="font-bold flex-grow-0">To:</span>

      <PDatePicker
          v-model="end"
          :minDate
          :dateFormat="fieldFormat" />
      <JetInputError :message="endError" />
    </label>
  </div>
</template>
