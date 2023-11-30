<script setup>
import JetInputError from "@/Jetstream/InputError.vue";
import Datepicker from "@vuepic/vue-datepicker";
import {intlFormat, isAfter, isBefore, parseISO} from "date-fns";
import formatISO from "date-fns/formatISO";
import {computed, inject, watch} from 'vue';

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
})

const isDarkMode = inject('darkMode', false)

const emit = defineEmits([
    'update:startDate',
    'update:endDate',
])

const formatForIso = (date) => formatISO(date, {representation: 'date'});

/**
 * @param {Date} date
 * @returns {string}
 */
const fieldFormat = date => intlFormat(date, {day: 'numeric', month: 'short', year: 'numeric'})

const start = computed({
    get: () => props.startDate,
    set: (value) => emit('update:startDate', formatForIso(value))
})
const end = computed({
    get: () => props.endDate,
    set: (value) => emit('update:endDate', formatForIso(value))
})

watch(start, (value) => {
    if (value && end.value && isAfter(parseISO(value), parseISO(end.value))) {
        end.value = parseISO(value)
    }
})
watch(end, (value) => {
    if (value && start.value && isBefore(parseISO(value), parseISO(start.value))) {
        start.value = parseISO(value)
    }
})
</script>

<template>
    <div class="grid grid-cols-[auto_minmax(0,_1fr)] gap-2 items-center sm:flex sm:space-x-4">
        <label class="contents">
            <div class="font-bold flex-grow-0">From:</div>
            <Datepicker auto-apply v-model="start" :enable-time-picker="false"
                        :format="fieldFormat" :clearable="false" :month-change-on-scroll="false" :dark="isDarkMode"
                        class="vacation flex-grow-0"/>
            <JetInputError :message="startError"/>
        </label>
        <label class="contents">
            <div class="font-bold flex-grow-0">To:</div>
            <Datepicker auto-apply v-model="end" :enable-time-picker="false"
                        :format="fieldFormat" :clearable="false" :month-change-on-scroll="false" :dark="isDarkMode"
                        class="vacation flex-grow-0"/>
            <JetInputError :message="endError"/>
        </label>
    </div>
</template>
