<script setup lang="ts">
import Slider from "@vueform/slider";
import { computed } from "vue";
import { numberOfWeeks } from "@/Composables/useAvailabilityActions";
import type { FormattedTooltip } from "@/Composables/useAvailabilityActions";

const { hoursEachDay, numberOfDaysPerMonth, shiftsPerDay, start, end, label, tooltipFormat } = defineProps<{
  hoursEachDay: App.Enums.AvailabilityHours[];
  numberOfDaysPerMonth: number;
  shiftsPerDay: string;
  start: number;
  end: number;
  label: string;
  tooltipFormat: FormattedTooltip;
}>();

const emit = defineEmits([
  "update:hoursEachDay",
  "update:numberOfDaysPerMonth",
  "update:shiftsPerDay",
]);

const hoursEachDayModel = computed({
  get: () => hoursEachDay,
  set: (value) => emit("update:hoursEachDay", value),
});

const numberOfDaysPerMonthModel = computed({
  get: () => numberOfDaysPerMonth,
  set: (value) => emit("update:numberOfDaysPerMonth", value),
});

const shiftsPerDayModel = computed({
  get: () => shiftsPerDay,
  set: (value) => emit("update:shiftsPerDay", value),
});

const options = Object.entries(numberOfWeeks).map(([value, label]) => ({ value: Number(value), label }));

const shiftsPerDayOptions = [
  { label: "1 shift", value: "1 shift" },
  { label: "2 shifts back-to-back", value: "2 shifts back-to-back" },
  { label: "2 shifts break-in-between", value: "2 shifts break-in-between" },
];
</script>

<template>
  <TransitionGroup enter-from-class="opacity-0"
                   enter-active-class="transition duration-300 ease-in"
                   enter-to-class="opacity-100"
                   leave-from-class="opacity-100"
                   leave-active-class="transition duration-300 ease-in"
                   leave-to-class="opacity-0">
    <div v-if="numberOfDaysPerMonthModel > 0" class="col-span-2 bg-transparent lg:col-span-2">
      {{ label }}
    </div>
    <PSelect v-if="numberOfDaysPerMonthModel > 0"
             v-model="numberOfDaysPerMonthModel"
             :options
             option-label="label"
             option-value="value"
             class="col-span-12 lg:col-span-5 bg-text-input dark:bg-text-input-dark" />

    <PSelect v-if="numberOfDaysPerMonthModel > 0"
             v-model="shiftsPerDayModel"
             :options="shiftsPerDayOptions"
             option-label="label"
             option-value="value"
             placeholder="Shifts per day"
             class="col-span-12 lg:col-span-5 bg-text-input dark:bg-text-input-dark" />

    <div v-if="numberOfDaysPerMonthModel > 0"
         class="grid col-span-12 items-center pb-6 bg-transparent border-b border-gray-500 border-dashed lg:pb-4 lg:border-b-0">
      <Slider v-model="hoursEachDayModel" range :min="start" :max="end" :format="tooltipFormat" />
    <!--      <PSlider v-model="hoursEachDayModel" range :min="start" :max="end" :format="tooltipFormat" /> -->
    </div>
  </TransitionGroup>
</template>
