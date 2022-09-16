<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import EmptySlot from '@/Components/Icons/EmptySlot.vue'
    import Female from '@/Components/Icons/Female.vue'
    import Male from '@/Components/Icons/Male.vue'
    import SelectField from '@/Components/SelectField.vue'
    import useToast from '@/Composables/useToast'
    import JetButton from '@/Jetstream/Button.vue'
    import JetConfirmModal from '@/Jetstream/ConfirmationModal.vue'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    import DatePicker from '@/Pages/Components/Dashboard/DatePicker.vue'
    import axios from 'axios'
    import { format } from 'date-fns'
    // noinspection ES6UnusedImports
    import { VTooltip } from 'floating-vue'
    import { ref } from 'vue'

    defineProps({
        user: Object,
    })

    const toast = useToast()

    const { date, locations, serverDates, getShifts, emptyShiftsForTime } = useLocationFilter(true)

    const gridCols = {
        // See tailwind.config.js
        1: 'grid-cols-reservation-1',
        2: 'grid-cols-reservation-2',
        3: 'grid-cols-reservation-3',
        4: 'grid-cols-reservation-4',
    }

    const locationsOnDays = ref([])

    const setLocationMarkers = locations => locationsOnDays.value = locations

    const hasShiftsForTime = (shiftTime, locationId) =>
        !!emptyShiftsForTime.value?.find((shiftData) =>
            shiftTime === shiftData.time && locationId !== shiftData.locationId)

    const shiftsForTime = (shiftTime, locationId) => emptyShiftsForTime.value
        ?.filter((shiftData) => shiftTime === shiftData.time && locationId !== shiftData.locationId)
        ?.map(({ location, locationId, currentVolunteers }) => {
            const label = location

            const volunteers = currentVolunteers.map(volunteer => {
                const prefix = volunteer.gender === 'male' ? 'Bro' : 'Sis'
                return `${prefix} ${volunteer.name}`
            })
            // const label = `<strong class="font-bold">${location}</strong>` + '<ul class="pl-3">' + currentVolunteers.map(volunteer => {
            //     const prefix = volunteer.gender === 'male' ? 'Bro' : 'Sis'
            //     return `<li class="list-disc">${prefix} ${volunteer.name}</li>`
            // }).join('')
            return { label, volunteers, id: locationId }
        })

    const selectedMoveUser = ref(null)

    const promptMoveUser = (selection, volunteer, shift) => selectedMoveUser.value = { selection, volunteer, shift }

    const doMoveUser = async (userId, locationId, shiftId) => {
        selectedMoveUser.value = null
        try {
            await axios.put('/admin/move-user-to-shift', {
                user_id: userId,
                location_id: locationId,
                old_shift_id: shiftId,
                date: format(date.value, 'yyyy-MM-dd'),
            })
            toast.success('User was moved!')
        } catch (e) {
            console.log(e)
            toast.error(e.response.data.message)

        } finally {
            getShifts()
        }
    }
</script>

<template>
    <div class="grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto]">
        <div class="pb-3 md:pb-0 justify-self-center">
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
                    <span class="dark:text-gray-200">{{ label }}</span>
                </template>
                <template v-slot="{location}">
                    <div class="w-full grid gap-x-2 gap-y-4" :class="gridCols[location.max_volunteers]">
                        <template v-for="shift in location.filterShifts" :key="shift.id">
                            <div class="self-center pl-3 dark:text-gray-100">
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
                            <div class="col-span-full bg-slate-200 dark:bg-slate-700 dark:text-gray-50 rounded px-3 py-2">
                                <ul>
                                    <li v-for="(volunteer, index) in shift.filterVolunteers"
                                        :key="index"
                                        class="border-b border-gray-400 last:border-b-0 py-2 flex justify-between">
                                        <template v-if="volunteer">
                                            <div class=" flex items-center">
                                                <div class="w-full md:w-auto md:mr-3">
                                                    {{ volunteer.gender === 'male' ? 'Bro' : 'Sis' }}
                                                    {{ volunteer.name }}
                                                </div>
                                                <div class="w-full md:w-auto">
                                                    <template v-if="hasShiftsForTime(shift.start_time, location.id)">
                                                        <SelectField @update:modelValue="promptMoveUser($event, volunteer, shift)"
                                                                     :options="shiftsForTime(shift.start_time, location.id)"
                                                                     select-label="Change location">
                                                            <template #extra="{option}">
                                                                <ul class="pl-3 font-normal">
                                                                    <li v-for="volunteer in option.volunteers"
                                                                        :key="volunteer"
                                                                        class="list-disc">
                                                                        {{ volunteer }}
                                                                    </li>
                                                                </ul>
                                                            </template>
                                                        </SelectField>
                                                    </template>
                                                    <template v-else>
                                                        <span class="text-red-500">No shifts available</span>
                                                    </template>
                                                </div>
                                            </div>
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
    <JetConfirmModal v-model:show="selectedMoveUser">
        <template #title>
            <h2 class="text-lg font-medium text-gray-900">Move user</h2>
        </template>
        <template #content>
            <div>
                Are you sure you want to move {{ selectedMoveUser.volunteer.name }} to
                {{ selectedMoveUser.selection.label }}?
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <JetButton style-type="secondary" @click="selectedMoveUser = null">Cancel</JetButton>
                <JetButton @click="doMoveUser(selectedMoveUser?.volunteer.id, selectedMoveUser?.selection.id, selectedMoveUser?.shift.id)"
                           class="ml-2">
                    Move
                </JetButton>
            </div>
        </template>
    </JetConfirmModal>
</template>
