<script setup>
//https://github.com/vueform/slider
import SelectField from "@/Components/SelectField.vue";
import Slider from "@vueform/slider";
import {computed} from "vue";

const props = defineProps({
    hoursEachDay: Array,
    numberOfDaysPerMonth: Number,
    start: Number,
    end: Number,
    numberOfWeeks: Array,
    label: String,
    tooltipFormat: Function,
});

const emit = defineEmits([
    'update:hoursEachDay',
    'update:numberOfDaysPerMonth',
]);

const hoursEachDayModel = computed({
    get: () => props.hoursEachDay,
    set: value => emit('update:hoursEachDay', value),
});

const numberOfDaysPerMonthModel = computed({
    get: () => props.numberOfDaysPerMonth,
    set: value => emit('update:numberOfDaysPerMonth', value),
});
</script>

<template>
    <Transition mode="out-in">
        <div v-show="numberOfDaysPerMonthModel > 0"
             class="col-span-2 bg-slate-200 dark:bg-slate-800 p-2 grid self-center">
            {{ label }}
        </div>
    </Transition>
    <Transition mode="out-in">
        <SelectField v-show="numberOfDaysPerMonthModel > 0" return-object-value full-width-button
                     v-model="numberOfDaysPerMonthModel" :options="numberOfWeeks"
                     class="col-span-3 bg-slate-200 dark:bg-slate-800 p-2 mr-2"/>
    </Transition>
    <Transition mode="out-in">
        <div v-show="numberOfDaysPerMonthModel > 0"
             class="col-span-7 bg-slate-200 dark:bg-slate-800 p-2 grid items-center pb-6 sm:pb-0 border-b border-gray-500">
            <Slider v-model="hoursEachDayModel" :min="start" :max="end" :format="tooltipFormat"/>
        </div>
    </Transition>
</template>

<style lang="scss" scoped>
.v-enter-active,
.v-leave-active {
    transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}
</style>
