<script setup>
import Datepicker from "@vuepic/vue-datepicker";
import {intlFormat} from "date-fns";
import formatISO from "date-fns/formatISO";
import {computed, inject} from 'vue';

const props = defineProps({
    startDate: {
        type: String,
    },
    endDate: {
        type: String,
    },
})

const isDarkMode = inject('darkMode', false)

const emit = defineEmits([
    'update:startDate',
    'update:endDate',
])

const parseDate = (date) => formatISO(date, {representation: 'date'});

/**
 * @param {Date} date
 */
const formatDate = (date) => intlFormat(date, {day: 'numeric', month: 'numeric', year: 'numeric'});

/**
 * @param {Array<Date, Date>} dates
 * @returns {string}
 */
const formatDateRange = (dates) => formatDate(dates[0]) + ' - ' + formatDate(dates[1]);

const dates = computed({
    get: () => {
        return [props.startDate, props.endDate]
    },
    set: (value) => {
        emit('update:startDate', parseDate(value[0]))
        emit('update:endDate', parseDate(value[1]))
    }
})
</script>

<template>
    <Datepicker range auto-apply :multi-calendars="{solo: true}" v-model="dates" :enable-time-picker="false"
                :format="formatDateRange" :clearable="false" :month-change-on-scroll="false" :dark="isDarkMode"
                class="vacation"/>
</template>
