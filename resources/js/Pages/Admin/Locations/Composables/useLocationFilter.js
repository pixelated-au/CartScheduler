import {endOfDay, endOfMonth, getMonth, isAfter, isBefore, parse, setDate, setHours} from 'date-fns';
import {utcToZonedTime} from "date-fns-tz";
import formatISO from 'date-fns/formatISO';
import {cloneDeep} from 'lodash';
import {computed, onMounted, ref, shallowRef, watch} from 'vue';

/**
 * @typedef { import("vue").Ref } Ref
 */
export default function useLocationFilter(timezone, canAdmin = false) {

    /**
     * @param {Ref<Date>} date
     */
    const date = ref(setHours(utcToZonedTime(new Date(), timezone.value), 12));
    /**
     * Represents the maximum reservation date.
     * @constant
     * @type {Ref<Date|null>}
     * @default {Ref<Date|null>}
     */
    const maxReservationDate = ref(endOfDay(utcToZonedTime(new Date(), timezone.value)));
    const serverLocations = shallowRef([]);
    const serverDates = shallowRef({});
    const freeShifts = shallowRef([]);
    const isLoading = ref(false);

    const getShifts = async (showLoader = true) => {
        let timeoutId;
        if (showLoader) {
            timeoutId = setTimeout(() => isLoading.value = true, 1000);
        }
        try {
            const path = canAdmin ? `/admin/assigned-shifts/${formattedDate.value}` : `/shifts/${formattedDate.value}`;

            const response = await axios.get(path);
            serverLocations.value = response.data.locations;
            serverDates.value = response.data.shifts;

            // next two props used in non-admin view
            freeShifts.value = response.data.freeShifts;
            maxReservationDate.value = response.data.maxDateReservation ? endOfDay(utcToZonedTime(response.data.maxDateReservation, timezone.value)) : null;
        } finally {
            if (showLoader) {
                isLoading.value = false;
                clearTimeout(timeoutId);
            }
        }
    };

    onMounted(() => {
        getShifts().then(() => {
        });
    });

    const formattedDate = computed(() =>
        date.value
            ? formatISO(date.value, {representation: 'date'})
            : '',
    );

    /**
     * This will ensure if the month goes forward, the date is set to the first day of the month
     * and if the month goes backward, the date is set to the last day of the previous month
     */
    watch(date, async (newDate, previousDate) => {
        const newMonth = getMonth(newDate);
        const oldMonth = getMonth(previousDate);
        if (newMonth === undefined || newMonth === null) {
            return;
        }

        // going forward in time, set the date to the first day of the next month
        // Both admin and non-admin users have a maxReservationDate
        if (newMonth > oldMonth) {
            date.value = setDate(newDate, 1);
            return;
        }

        // going back in time, set the date to the last day of the previous month
        if (newMonth < oldMonth) {
            date.value = endOfMonth(newDate);
            return;
        }
        getShifts().then(() => {
        });
    });

    const emptyShiftsForTime = ref([]);

    const setReservations = (maxVolunteers, shift, location) => {
        shift.freeShifts = 0;

        const volunteers = shift.filterVolunteers?.sort((a, b) => {
            // First, compare genders
            if (a.gender !== b.gender) {
                return b.gender.localeCompare(a.gender);
            }
            // If genders are the same, compare shift_id
            return a.shift_id - b.shift_id;
        });

        const length = maxVolunteers >= volunteers.length ? maxVolunteers - volunteers.length : maxVolunteers;

        if (length) {
            shift.freeShifts = length;
            const nullArray = Array(length).fill(null);
            shift.filterVolunteers = [...volunteers, ...nullArray];

            emptyShiftsForTime.value.push({
                // no need to timezone here, because we are only extracting the time
                startTime: parse(shift.start_time, 'HH:mm:ss', date.value),
                endTime: parse(shift.end_time, 'HH:mm:ss', date.value),
                location: location.name,
                locationId: location.id,
                currentVolunteers: volunteers,
                days: shift.js_days,
                available_from: shift.available_from,
                available_to: shift.available_to,
            });
        }
    };

    const addShift = (shifts, shift) => {
        if (shift.available_from) {
            const from = utcToZonedTime(shift.available_from, timezone.value);
            if (isBefore(date.value, from)) {
                return false;
            }
        }
        if (shift.available_to) {
            const to = utcToZonedTime(`${shift.available_to}T23:59:59`, timezone.value);
            if (isAfter(date.value, to)) {
                return false;
            }
        }
        shifts.push(shift);
        return true;
    };

    const addLocation = (mappedLocations, location, shift) => {
        const alreadyAddedLocation = mappedLocations.find(l => l.id === location.id);
        if (!alreadyAddedLocation) {
            location.filterShifts = [];
            if (addShift(location.filterShifts, shift)) {
                mappedLocations.push(location);
            }
        } else {
            if (!alreadyAddedLocation.filterShifts.find(s => s.id === shift.id)) {
                addShift(alreadyAddedLocation.filterShifts, shift);
            }
        }
    };
    /**
     * @typedef {Object} LocationData
     * @property {number} id - The location's identifier.
     * @property {string} name - The location's name.
     * @property {boolean} requires_brother - Indicates if the location requires a brother.
     * @property {number} max_volunteers - The maximum number of volunteers that the location can accommodate.
     * @property {boolean} hasFreeShifts - Indicates if the location has available shifts.
     * @property {FilterShift[]} filterShifts - The filtered shifts for the location.
     *
     * @typedef {Object} FilterShift
     * @property {number} id - The shift's identifier.
     * @property {Object[]} volunteers - The volunteers for the shift.
     * @property {Object[]} filterVolunteers - The volunteers filtered based on the selected date.
     * @property {boolean} maxedFemales - Indicates if the maximum number of female volunteers has been reached.
     * @property {boolean} hasFreeShifts - Indicates if the shift has available slots for volunteers.
     * @property {Object[]} js_days - Shows the availability of the shift throughout the week.
     */

    /**
     * @type {import('vue').ComputedRef<LocationData[]>} - An array of locations that have available shifts.
     *
     * This computed function filters and maps server locations and then returns an array of locations
     * which have available shifts. The availability of shifts is determined based on the number of
     * volunteers and the selected date. The function also takes into account the gender requirement
     * of a location and whether the maximum number of female volunteers has been reached for
     * each location.
     */
    const locations = computed(() => {
        if (!serverLocations?.value) {
            return [];
        }
        /** @type {LocationData[]} */
        const mappedLocations = [];
        let myLocations = cloneDeep(serverLocations.value);
        emptyShiftsForTime.value = [];

        for (const location of myLocations) {
            location.freeShifts = 0;
            /** @type {number} */
            let freeShifts = 0;
            for (const shift of location.shifts) {
                const volunteers = shift.volunteers;
                shift.filterVolunteers = volunteers.filter(volunteer => volunteer.shift_date === formattedDate.value);
                delete shift.volunteers;
                if (location.requires_brother) {
                    let femaleCount = 0;
                    for (const filVolunteer of shift.filterVolunteers) {
                        if (filVolunteer.gender === 'female') {
                            femaleCount++;
                        }
                    }
                    shift.maxedFemales = femaleCount >= location.max_volunteers - 1;
                }
                setReservations(location.max_volunteers, shift, location);

                freeShifts += shift.freeShifts;
                const dayOfWeek = date.value.getDay();
                const mappedDay = shift.js_days[dayOfWeek];
                if (mappedDay === true) {
                    addLocation(mappedLocations, location, shift);
                }
            }

            location.freeShifts += freeShifts;
            delete location.shifts;
        }
        // just to clear up some memory
        myLocations = null;

        return mappedLocations;
    });

    return {
        date,
        emptyShiftsForTime,
        freeShifts,
        isLoading,
        locations,
        maxReservationDate,
        serverDates,
        getShifts,
    };
}
