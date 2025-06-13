<script setup>
import { usePage } from "@inertiajs/vue3";
import {
    addDays,
    addMonths,
    endOfDay,
    endOfMonth,
    isAfter,
    isBefore,
    lastDayOfMonth,
    parseISO,
    set,
    setHours,
    startOfDay,
    startOfMonth,
    subMonths,
} from "date-fns";
import { utcToZonedTime } from "date-fns-tz";
import { computed, defineEmits, defineProps, inject, onMounted, ref, watchEffect } from "vue";

const props = defineProps({
    date: Date,
    maxDate: Date,
    locations: Array,
    markerDates: Object,
    user: Object,
    freeShifts: Object,
    canViewHistorical: Boolean,
    showLegend: {
        type: Boolean,
        default: false,
    },
    class:  String,
});

const emit = defineEmits([
    "update:date",
    "locations-for-day",
]);

const selectedDate = computed({
    get: () => props.date,
    // set the date at midday to be safe...
    set: (value) => emit("update:date", set(value, { hours: 12, minutes: 0, seconds: 0, milliseconds: 0 })),
});

const shiftAvailability = computed(() => {
    return usePage().props.shiftAvailability;
});

const isRestricted = computed(() => {
    return !usePage().props.isUnrestricted;
});

const today = setHours(new Date(), 12);
const notBefore = props.canViewHistorical
    ? startOfDay(startOfMonth(subMonths(today, 6)))
    : startOfDay(today);

// Note, this is computed because props.maxDate is a reactive value whereas notAfter has no reactivity dependency
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
    if (!props.freeShifts) return highlighted;

    for (const key in props.freeShifts) {
        if (!Object.prototype.hasOwnProperty.call(props.freeShifts, key)) {
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
    return markers.value.map((marker) => marker.date);
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
        if (!Object.prototype.hasOwnProperty.call(props.markerDates, date)) {
            continue;
        }
        const foundAtLocation = [];
        const shiftDateGroup = props.markerDates[date];

        let isoDate = undefined;
        for (const shiftId in shiftDateGroup) {
            if (!Object.prototype.hasOwnProperty.call(shiftDateGroup, shiftId)) {
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
                type: "line",
                color: "#0E9F6E",
                locations: foundAtLocation,
            });
        }
    }

    markers.value = marks;

    emit("locations-for-day", marks.map((marker) => ({ locations: marker.locations, date: marker.date })));
});

/**
 * @description Used to set the date when the user changes month (or year). This ensures that the next month's values
 * are loaded.
 */
const updateMonthYear = (event) => {
    const { month, year } = event;
    // Setting the 'day of month' to 0, sets the day to the previous month's last day
    const totalDays = lastDayOfMonth(new Date(year, month, 1, 0, 0, 0, 0));
    const currentDate = selectedDate.value;
    if (isAfter(currentDate, totalDays)) {
        // Going back in time, set the date to the last day of the previous month
        selectedDate.value = new Date(year, month, totalDays.getDate());
        return;
    }
    // Going forward in time. Check next month has enough days, i.e. if the month is February, check if the date is
    // less than the previous month's selected date
    const dayOfMonth = currentDate.getDate() > totalDays.getDate()
        ? totalDays.getDate()
        : currentDate.getDate();
    selectedDate.value = new Date(year, month, dayOfMonth);
};

// Function to check if a date is highlighted (has free shifts)
const isDateHighlighted = (date) => {
    if (!props.freeShifts || !highlights.value.length) return false;

    const dateObj = new Date(date.year, date.month, date.day);

    return highlights.value.some((d) =>
        d.getDate() === dateObj.getDate() &&
        d.getMonth() === dateObj.getMonth() &&
        d.getFullYear() === dateObj.getFullYear());
};

// Function to check if a date has a marker (user's shifts)
const hasMarker = (date) => {
    if (!markers.value.length) return false;

    const dateObj = new Date(date.year, date.month, date.day);

    return markers.value.some((m) =>
        m.date.getDate() === dateObj.getDate() &&
        m.date.getMonth() === dateObj.getMonth() &&
        m.date.getFullYear() === dateObj.getFullYear());
};

// Function to disable dates that are not allowed
const disabledDates = (date) => {
    if (!isRestricted.value || !allowed.value || !allowed.value.length) return false;

    const dateObj = new Date(date.year, date.month, date.day);

    return !allowed.value.some((d) =>
        d.getDate() === dateObj.getDate() &&
        d.getMonth() === dateObj.getMonth() &&
        d.getFullYear() === dateObj.getFullYear());
};
</script>

<template>
<PDatePicker v-model="selectedDate"
             inline
             :minDate="notBefore"
             :maxDate="notAfter"
             :disabled="false"
             :showIcon="false"
             :showButtonBar="false"
             :dateFormat="'mm/dd/yy'"
             :disabledDates="isRestricted ? disabledDates : null"
             @month-change="updateMonthYear"
             @year-change="updateMonthYear">
  <template #date="{ date }">
    <span class="formatted-date"
          :class="{
            'highlighted-date': isDateHighlighted(date),
            'marker-date': hasMarker(date)
          }">
      {{ date.day }}
    </span>
  </template>
</PDatePicker>
<div v-if="showLegend" class="legend text-xs mt-2">Blue squares indicate free shifts</div>
</template>
