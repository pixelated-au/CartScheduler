<script setup lang="ts">
import { parseISO, addDays, formatISO  } from "date-fns";
import { computed, watch, ref } from "vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";

const props = defineProps<{
  startDate?: string;
  endDate?: string;
  startError?: string;
  endError?: string;
}>();

const emit = defineEmits([
  "update:startDate",
  "update:endDate",
]);

const formatForIso = (date: Date) => formatISO(date, { representation: "date" });
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
  <div class="grid grid-cols-2 gap-4 grid-rows-1 items-center">
    <div class="grid grid-cols-1 gap-x-2 gap-y-1">
      <JetLabel class="font-bold" :has-error="!!startError" value="From"/>

      <PDatePicker v-model="start"
                   :minDate="new Date()"
                   :maxDate
                   :dateFormat="fieldFormat" />
      <JetInputError :message="startError" />
    </div>
    <div class="grid grid-cols-1 gap-2">
      <JetLabel class="font-bold" :has-error="!!endError" value="To"/>

      <PDatePicker v-model="end"
                   :minDate
                   :dateFormat="fieldFormat" />
      <JetInputError :message="endError" />
    </div>
  </div>
</template>
