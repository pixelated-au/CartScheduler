<script setup>
import ComponentSpinner from '@/Components/ComponentSpinner.vue';
import BookedSlot from '@/Components/Icons/BookedSlot.vue';
import EmptySlot from '@/Components/Icons/EmptySlot.vue';
import Female from '@/Components/Icons/Female.vue';
import Male from '@/Components/Icons/Male.vue';
import Loading from "@/Components/Loading.vue";
import Accordion from '@/Components/LocationAccordion.vue';
import useToast from '@/Composables/useToast';
import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter';
import DatePicker from '@/Pages/Components/Dashboard/DatePicker.vue';
import {usePage} from "@inertiajs/inertia-vue3";
import {format, isSameDay, parse} from 'date-fns';
// noinspection ES6UnusedImports
import {VTooltip} from 'floating-vue';
import {computed, ref} from 'vue';

defineProps({
    user: Object,
});

const toast = useToast();

const {date, freeShifts, isLoading, locations, maxReservationDate, serverDates, getShifts} = useLocationFilter();

const gridCols = {
    // See tailwind.config.js
    1: 'grid-cols-sm-reservation-1 sm:grid-cols-reservation-1',
    2: 'grid-cols-sm-reservation-2 sm:grid-cols-reservation-2',
    3: 'grid-cols-sm-reservation-3 sm:grid-cols-reservation-3',
    4: 'grid-cols-sm-reservation-4 sm:grid-cols-reservation-4',
    5: 'grid-cols-sm-reservation-5 sm:grid-cols-reservation-5',
};

const toggleReservation = async (locationId, shiftId, toggleOn) => {
    try {
        const response = await axios.post('/reserve-shift', {
            location: locationId,
            shift: shiftId,
            do_reserve: toggleOn,
            date: format(date.value, 'yyyy-MM-dd'),
        });
        if (toggleOn) {
            toast.success(response.data);
        } else {
            toast.warning(response.data);
        }
        await getShifts();

    } catch (e) {
        toast.error(e.response.data.message, {timeout: 4000});
        if (e.response.data.error_code === 100) {
            await getShifts();
        }
    }
};

const locationsOnDays = ref([]);
const flagDates = computed(() => locationsOnDays.value.filter(location => isSameDay(location.date, date.value)));

const setLocationMarkers = locations => locationsOnDays.value = locations;
const isMyShift = location => {
    return flagDates.value?.findIndex(d => d?.locations.includes(location.id)) >= 0;
};

const today = new Date();
const formatTime = time => format(parse(time, 'HH:mm:ss', today), 'h:mm a');

const isRestricted = computed(() => {
    return !usePage().props.value.isUnrestricted;
});

const locationLabel = computed(() => {
    const labelData = {};
    for (const location of locations.value) {
        const classes = [];
        let tooltip = undefined;
        if (isMyShift(location)) {
            classes.push(...['text-green-800', 'dark:text-green-300', 'border-b-2', 'border-green-500']);
            tooltip = 'You have at least one shift';
        } else {
            classes.push('dark:text-gray-200');
        }
        labelData[location.id] = {classes, tooltip};
    }
    return labelData;
});

</script>

<template>
    <div class="p-3 grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto] sm:items-stretch">
        <div class="pb-3">
            <ComponentSpinner :show="!locations">
                <DatePicker v-model:date="date"
                            :max-date="maxReservationDate"
                            :locations="locations"
                            :free-shifts="freeShifts"
                            :user="user"
                            :marker-dates="serverDates"
                            @locations-for-day="setLocationMarkers"/>
            </ComponentSpinner>
            <div class="text-sm text-gray-500 text-center">Blue squares indicate free shifts</div>
        </div>
        <div class="text-sm">
            <Loading v-if="isLoading" class="min-h-[200px] sm:min-h-full"/>
            <Accordion v-show="!isLoading"
                       :items="locations"
                       :expand-first="locations.length === 1"
                       label="name" uid="id"
                       empty-collection-text="No available locations for this day">
                <template #label="{label, location}">
                    <div class="flex items-center">
                        <span :class="locationLabel[location.id].classes" v-tooltip="locationLabel[location.id].tooltip">{{ label }}</span>
                        <div class="ml-2 py-1.5 group flex items-center" v-if="!isRestricted && location.freeShifts">
                            <div
                                class="ml-1 mr-3 w-2 h-2 rounded-full bg-amber-500 group-hover:bg-amber-600 group-hover:dark:bg-amber-200 transition-colors"></div>
                            <div class="min-w-5 hidden sm:block">
                                <div
                                    class="w-0 overflow-x-hidden whitespace-nowrap group-hover:w-full text-gray-600 dark:text-gray-400 text-sm transition-all">
                                    shifts still available
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-slot="{location}">
                    <div class="w-full">
                        <div v-if="!isRestricted && location.freeShifts" class="sm:hidden ml-3 mb-2 group flex">
                        <div class="px-2 py-0.5 flex items-center border rounded-full border-amber-500 dark:border-amber-600">
                            <div
                                class="mr-1 w-2 h-2 rounded-full bg-amber-600 dark:bg-amber-500"></div>
                            <div class="text-amber-600 dark:text-amber-500 text-sm">free shifts still available at this location</div>
                        </div>
                        </div>

                        <div v-html="location.description" class="description w-full p-3 pt-0 dark:text-gray-100"></div>
                        <div class="w-full grid gap-x-2 gap-y-2 sm:gap-y-4" :class="gridCols[location.max_volunteers]">
                            <template v-for="shift in location.filterShifts" :key="shift.id">
                                <div class="self-center pl-3 sm:pr-4 pt-4 dark:text-gray-100">
                                    {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
                                </div>
                                <div v-for="(volunteer, index) in shift.filterVolunteers"
                                     :key="index"
                                     class="self-center justify-self-center pt-4">
                                    <template v-if="volunteer">
                                        <template v-if="volunteer.id === user.id">
                                            <button v-if="!isRestricted"
                                                    type="button"
                                                    class="block"
                                                    @click="toggleReservation(location.id, shift.id, false)">
                                                <BookedSlot
                                                    v-tooltip="`${volunteer.name}: Tap to un-reserve this shift`"/>
                                            </button>
                                            <BookedSlot v-else/>
                                        </template>
                                        <Male v-else-if="volunteer.gender === 'male'" v-tooltip="volunteer.name"/>
                                        <Female v-else-if="volunteer.gender === 'female'" v-tooltip="volunteer.name"/>
                                    </template>
                                    <EmptySlot v-else-if="isRestricted" v-tooltip="'You cannot reserve a shift'"/>
                                    <EmptySlot
                                        v-else-if="index === shift.filterVolunteers.length - 1 && shift.maxedFemales && user.gender === 'female'"
                                        color="#79B9ED"
                                        v-tooltip="'This slot can only be reserved by a brother'"/>
                                    <button v-else
                                            type="button"
                                            class="block"
                                            @click="toggleReservation(location.id, shift.id, true)">
                                        <EmptySlot v-tooltip="'Tap to reserve this shift'"/>
                                    </button>
                                </div>
                                <div
                                    class="col-span-full bg-slate-200 dark:bg-slate-700 dark:text-gray-50 rounded px-3 sm:py-2">
                                    <ul>
                                        <li v-for="(volunteer, index) in shift.filterVolunteers"
                                            :key="index"
                                            class="border-b border-gray-400 last:border-b-0 py-2 flex justify-between">
                                            <template v-if="volunteer">
                                                <div>{{ volunteer.name }}</div>
                                                <div>Ph: <a :href="`tel:${volunteer.mobile_phone}`">{{
                                                        volunteer.mobile_phone
                                                    }}</a></div>
                                            </template>
                                            <template v-else>
                                                <div>—</div>
                                            </template>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </Accordion>
        </div>
    </div>
</template>

<style lang="scss">
.description {
    p {
        @apply mb-3;
    }

    ul, ol {
        @apply pl-5;
        li p {
            @apply mb-0.5;
        }
    }

    ul {
        @apply list-disc;
    }

    ol {
        @apply list-decimal;
    }

    strong {
        @apply font-bold
    }
}
</style>
