<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import EmptySlot from '@/Components/Icons/EmptySlot.vue'
    import Female from '@/Components/Icons/Female.vue'
    import Male from '@/Components/Icons/Male.vue'
    import useToast from '@/Composables/useToast'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    import DatePicker from '@/Pages/Components/Dashboard/DatePicker.vue'
    import { isSameDay } from 'date-fns'
    // noinspection ES6UnusedImports
    import { VTooltip } from 'floating-vue'
    import { computed, ref } from 'vue'

    defineProps({
        user: Object,
    })

    const toast = useToast()

    const { date, locations, serverDates, getShifts } = useLocationFilter(true)

    const gridCols = {
        // See tailwind.config.js
        1: 'grid-cols-reservation-1',
        2: 'grid-cols-reservation-2',
        3: 'grid-cols-reservation-3',
        4: 'grid-cols-reservation-4',
    }

    const toggleReservation = async (locationId, shiftId, toggleOn) => {
        try {
            await axios.post('/reserve-shift', {
                location: locationId,
                shift: shiftId,
                do_reserve: toggleOn,
                date: date.value,
            })
            if (toggleOn) {
                toast.success('Reservation made')
            } else {
                toast.warning('Reservation removed')
            }
            await getShifts()

        } catch (e) {
            toast.error(e.response.data.message)
            if (e.response.data.error_code === 100) {
                await getShifts()
            }
        }
    }

    const locationsOnDays = ref([])
    const flagDate = computed(() => locationsOnDays.value.find(location => isSameDay(location.date, date.value)))

    const setLocationMarkers = locations => locationsOnDays.value = locations
    const isMyShift = location => flagDate.value?.location === location.id
</script>

<template>
    <div class="grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto]">
        <div class="bg-white pb-3 md:pb-0 justify-self-center">
            <DatePicker can-view-historical
                        v-model:date="date"
                        :locations="locations"
                        :user="user"
                        :marker-dates="serverDates"
                        @locations-for-day="setLocationMarkers"/>
        </div>
        <div class="text-sm">
            <Accordion :items="locations" label="name" uid="id">
                <template #label="{label, location}">
                    <span v-if="isMyShift(location)"
                          class="text-green-800 border-b-2 border-green-500"
                          v-tooltip="'You have at least one shift'">
                        {{ label }}
                    </span>
                    <span v-else>{{ label }}</span>
                </template>
                <template v-slot="{location}">
                    <div class="w-full grid gap-x-2 gap-y-4" :class="gridCols[location.max_volunteers]">
                        <template v-for="shift in location.filterShifts" :key="shift.id">
                            <div class="self-center pl-3">
                                {{ shift.start_time }} - {{ shift.end_time }}
                            </div>
                            <div v-for="(volunteer, index) in shift.filterVolunteers"
                                 :key="index"
                                 class="self-center justify-self-center">
                                <template v-if="volunteer">
                                    <Male v-if="volunteer.gender === 'male'" v-tooltip="volunteer.name"/>
                                    <Female v-else-if="volunteer.gender === 'female'" v-tooltip="volunteer.name"/>
                                </template>
                                <EmptySlot v-else v-tooltip="'Available shift'"/>
                            </div>
                            <div></div>
                            <div class="col-span-full bg-slate-200 rounded px-3 py-2">
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
                </template>
            </Accordion>
        </div>
    </div>
</template>
