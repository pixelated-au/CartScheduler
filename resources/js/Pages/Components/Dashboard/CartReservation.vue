<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import ComponentSpinner from '@/Components/ComponentSpinner.vue'
    import BookedSlot from '@/Components/Icons/BookedSlot.vue'
    import EmptySlot from '@/Components/Icons/EmptySlot.vue'
    import Female from '@/Components/Icons/Female.vue'
    import Male from '@/Components/Icons/Male.vue'
    import useToast from '@/Composables/useToast'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    import DatePicker from '@/Pages/Components/Dashboard/DatePicker.vue'
    import { format, isSameDay, parse } from 'date-fns'
    // noinspection ES6UnusedImports
    import { VTooltip } from 'floating-vue'
    import { computed, ref } from 'vue'

    defineProps({
        user: Object,
    })

    const toast = useToast()

    const { date, locations, maxReservationDate, serverDates, getShifts } = useLocationFilter()

    const gridCols = {
        // See tailwind.config.js
        1: 'grid-cols-sm-reservation-1 sm:grid-cols-reservation-1',
        2: 'grid-cols-sm-reservation-2 sm:grid-cols-reservation-2',
        3: 'grid-cols-sm-reservation-3 sm:grid-cols-reservation-3',
        4: 'grid-cols-sm-reservation-4 sm:grid-cols-reservation-4',
    }

    const toggleReservation = async (locationId, shiftId, toggleOn) => {
        try {
            const response = await axios.post('/reserve-shift', {
                location: locationId,
                shift: shiftId,
                do_reserve: toggleOn,
                date: format(date.value, 'yyyy-MM-dd'),
            })
            if (toggleOn) {
                toast.success(response.data)
            } else {
                toast.warning(response.data)
            }
            await getShifts()

        } catch (e) {
            toast.error(e.response.data.message, { timeout: 4000 })
            if (e.response.data.error_code === 100) {
                await getShifts()
            }
        }
    }

    const locationsOnDays = ref([])
    const flagDates = computed(() => locationsOnDays.value.filter(location => isSameDay(location.date, date.value)))

    const setLocationMarkers = locations => locationsOnDays.value = locations
    const isMyShift = location => {
        return flagDates.value?.findIndex(d => d?.locations.includes(location.id)) >= 0
    }

    const today = new Date()
    const formatTime = time => format(parse(time, 'HH:mm:ss', today), 'h:mm a')
</script>

<template>
    <div class="p-3 grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto]">
        <div class="pb-3">
            <ComponentSpinner :show="!locations?.length">
                <DatePicker v-model:date="date"
                            :max-date="maxReservationDate"
                            :locations="locations"
                            :user="user"
                            :marker-dates="serverDates"
                            @locations-for-day="setLocationMarkers"/>
            </ComponentSpinner>
            <div class="text-sm text-gray-500 text-center">Blue squares indicate free shifts</div>
        </div>
        <div class="text-sm">
            <Accordion :items="locations" label="name" uid="id">
                <template #label="{label, location}">
                    <span v-if="isMyShift(location)"
                          class="text-green-800 dark:text-green-300 border-b-2 border-green-500"
                          v-tooltip="'You have at least one shift'">
                        {{ label }}
                    </span>
                    <span v-else class="dark:text-gray-200">{{ label }}</span>
                </template>
                <template v-slot="{location}">
                    <div class="w-full">
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
                                        <button v-if="volunteer.id === user.id"
                                                type="button"
                                                class="block"
                                                @click="toggleReservation(location.id, shift.id, false)">
                                            <BookedSlot v-tooltip="`${volunteer.name}: Tap to un-reserve this shift`"/>
                                        </button>
                                        <Male v-else-if="volunteer.gender === 'male'" v-tooltip="volunteer.name"/>
                                        <Female v-else-if="volunteer.gender === 'female'" v-tooltip="volunteer.name"/>
                                    </template>
                                    <EmptySlot v-else-if="index === shift.filterVolunteers.length - 1 && shift.maxedFemales && user.gender === 'female'"
                                               color="#79B9ED"
                                               v-tooltip="'This slot can only be reserved by a brother'"/>
                                    <button v-else
                                            type="button"
                                            class="block"
                                            @click="toggleReservation(location.id, shift.id, true)">
                                        <EmptySlot v-tooltip="'Tap to reserve this shift'"/>
                                    </button>
                                </div>
                                <div class="col-span-full bg-slate-200 dark:bg-slate-700 dark:text-gray-50 rounded px-3 sm:py-2">
                                    <ul>
                                        <li v-for="(volunteer, index) in shift.filterVolunteers"
                                            :key="index"
                                            class="border-b border-gray-400 last:border-b-0 py-2 flex justify-between">
                                            <template v-if="volunteer">
                                                <div>{{ volunteer.name }}</div>
                                                <div>Ph: <a :href="`tel:${volunteer.mobile_phone}`"
                                                            class="underline underline-offset-4 decoration-dotted decoration-1 decoration-blue-800 visited:decoration-blue-800">{{ volunteer.mobile_phone
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

<style>
.description ul,
.description ol {
    padding-left: 1.5em;
    list-style: auto;
}
</style>
