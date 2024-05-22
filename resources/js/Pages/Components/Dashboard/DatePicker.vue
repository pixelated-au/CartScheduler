<script setup>
import {usePage} from '@inertiajs/inertia-vue3';

//https://vue3datepicker.com/
import Datepicker from '@vuepic/vue-datepicker';
import {
    addDays,
    addMonths,
    endOfDay,
    endOfMonth,
    isAfter,
    isBefore,
    parse,
    parseISO,
    startOfDay,
    startOfMonth,
    subMonths,
} from 'date-fns';
import {utcToZonedTime} from "date-fns-tz";
import {computed, defineEmits, defineProps, inject, onMounted, ref, watchEffect} from 'vue';

const props = defineProps({
    date: Date,
    maxDate: Date,
    locations: Array,
    markerDates: Object,
    user: Object,
    freeShifts: Object,
    canViewHistorical: Boolean,
});

const isDarkMode = inject('darkMode', false);

const emit = defineEmits([
    'update:date',
    'locations-for-day',
]);

const selectedDate = computed({
    get: () => props.date,
    // set the date at midday to be safe...
    set: (value) => emit('update:date', parse('23:59:59.999', 'HH:mm:ss.SSS', value)),
});

const shiftAvailability = computed(() => {
    return usePage().props.value.shiftAvailability;
});

const isRestricted = computed(() => {
    return !usePage().props.value.isUnrestricted;
});

const today = new Date();
const notBefore = props.canViewHistorical
    ? startOfDay(startOfMonth(subMonths(today, 6)))
    : startOfDay(today);

const notAfter = computed(() =>
    props.canViewHistorical
        ? endOfMonth(addMonths(notBefore, 12))
        : props.maxDate);

const allDates = [];
const getAllDates = () => {
    for (let i = notBefore; i < notAfter.value; i = addDays(i, 1)) {
        allDates.push(i);
    }
};

onMounted(() => {
    getAllDates();
});

const markers = ref([]);
const highlights = computed(() => {
    const highlighted = [];
    for (const key in props.freeShifts) {
        if (!props.freeShifts.hasOwnProperty(key)) {
            continue;
        }
        if (!props.freeShifts[key].has_availability) {
            continue;
        }
        highlighted.push(parseISO(key));
    }
    return highlighted;
});

const allowed = computed(() => {
    if (!isRestricted.value) {
        return null;
    }
    return markers.value.map(marker => marker.date);
});

watchEffect(() => {
    if (!props.markerDates) {
        return;
    }
    const marks = [];
    if (!props.user) {
        return [];
    }

    for (const date in props.markerDates) {
        if (!props.markerDates.hasOwnProperty(date)) {
            continue;
        }
        const foundAtLocation = [];
        const shiftDateGroup = props.markerDates[date];

        let isoDate = undefined;
        for (const shiftId in shiftDateGroup) {
            if (!shiftDateGroup.hasOwnProperty(shiftId)) {
                continue;
            }

            const shifts = shiftDateGroup[shiftId];
            for (let shiftCount = 0; shiftCount < shifts.length; shiftCount++) {
                const shift = shifts[shiftCount];
                if (!isoDate) {
                    isoDate = utcToZonedTime(shift.shift_date, shiftAvailability.value.timezone);
                }
                // TODO is this isBefore and isAfter still needed?
                if (isBefore(isoDate, startOfDay(parseISO(shift.available_from)))) {
                    break;
                }
                if (isAfter(isoDate, endOfDay(parseISO(shift.available_to)))) {
                    break;
                }
                if (shift.volunteer_id === props.user?.id) {
                    foundAtLocation.push(shift.location_id);
                }
            }
        }
        if (foundAtLocation.length) {
            marks.push({
                date: isoDate,
                type: 'line',
                color: '#0E9F6E',
                locations: foundAtLocation,
            });
        }
    }

    markers.value = marks;

    emit('locations-for-day', marks.map(marker => ({locations: marker.locations, date: marker.date})));
});

const updateMonthYear = ({month, year}) => {
    const totalDays = new Date(year, month + 1, 0).getDate();
    const monthDay = selectedDate.value.getDate();
    if (monthDay > totalDays) {
        selectedDate.value = new Date(year, month, totalDays);
        return;
    }
    selectedDate.value = new Date(year, month, selectedDate.value.getDate());
};
</script>

<template>
    <Datepicker inline
                auto-apply
                no-swipe
                prevent-min-max-navigation
                :enable-time-picker="false"
                v-model="selectedDate"
                :markers="markers"
                :highlight="highlights"
                :allowed-dates="allowed"
                :min-date="notBefore"
                :max-date="notAfter"
                :dark="isDarkMode"
                @update-month-year="updateMonthYear">
        <template #day="{day, date}">
            {{ day }}
        </template>
    </Datepicker>

</template>
