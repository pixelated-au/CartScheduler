<script setup lang="ts">
import Slider from "@vueform/slider";
import { computed } from "vue";

const { hoursEachDay, numberOfDaysPerMonth, start, end, numberOfWeeks, label, tooltipFormat } = defineProps<{
  hoursEachDay: number[];
  numberOfDaysPerMonth: number;
  start: number;
  end: number;
  numberOfWeeks: { label: string; value: number }[];
  label: string;
  tooltipFormat: (value: number) => string;
}>();

const emit = defineEmits([
  "update:hoursEachDay",
  "update:numberOfDaysPerMonth",
]);

const hoursEachDayModel = computed({
  get: () => hoursEachDay,
  set: (value) => emit("update:hoursEachDay", value),
});

const numberOfDaysPerMonthModel = computed({
  get: () => numberOfDaysPerMonth,
  set: (value) => emit("update:numberOfDaysPerMonth", value),
});
</script>

<template>
  <TransitionGroup enter-from-class="opacity-0"
                   enter-active-class="transition ease-in duration-300"
                   enter-to-class="opacity-100"
                   leave-from-class="opacity-100"
                   leave-active-class="transition ease-in duration-300"
                   leave-to-class="opacity-0">
    <div v-if="numberOfDaysPerMonthModel > 0" class="col-span-6 lg:col-span-2 bg-transparent">
      {{ label }}
    </div>
    <PSelect v-if="numberOfDaysPerMonthModel > 0"
             v-model="numberOfDaysPerMonthModel"
             :options="numberOfWeeks"
             option-label="label"
             option-value="value"
             class="col-span-6 lg:col-span-4 bg-text-input dark:bg-text-input-dark" />
    <div v-if="numberOfDaysPerMonthModel > 0"
         class="col-span-12 lg:col-span-6 bg-transparent grid items-center pb-6 lg:pb-0 border-b lg:border-b-0 border-dashed border-gray-500">
      <Slider v-model="hoursEachDayModel" range :min="start" :max="end" :format="tooltipFormat" />
      <!--      <PSlider v-model="hoursEachDayModel" range :min="start" :max="end" :format="tooltipFormat" /> -->
    </div>
  </TransitionGroup>
</template>
