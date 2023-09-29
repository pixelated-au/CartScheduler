<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import EmptySlot from '@/Components/Icons/EmptySlot.vue'
    import Female from '@/Components/Icons/Female.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetConfirmModal from '@/Jetstream/ConfirmationModal.vue'
    import Male from '@/Components/Icons/Male.vue'
    import useToast from '@/Composables/useToast'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    import DatePicker from '@/Pages/Components/Dashboard/DatePicker.vue'
    import axios from 'axios'
    import {format, parse} from 'date-fns'
    // noinspection ES6UnusedImports
    import {VTooltip} from 'floating-vue'
    import {computed, inject, reactive, ref} from 'vue'
    import UserActionsModal from "@/Pages/Admin/Dashboard/UserActionsModal.vue";
    import MoveUserSelectField from "@/Components/MoveUserSelectField.vue";
    import UserAdd from "@/Components/Icons/UserAdd.vue";
    import UserRemove from "@/Components/Icons/UserRemove.vue";

    defineProps({
        user: Object,
    })

    const toast = useToast()

    const {date, locations, serverDates, getShifts, emptyShiftsForTime} = useLocationFilter(true)

    const gridCols = {
        // See tailwind.config.js
        1: 'grid-cols-reservation-1',
        2: 'grid-cols-reservation-2',
        3: 'grid-cols-reservation-3',
        4: 'grid-cols-reservation-4',
        5: 'grid-cols-reservation-5',
    }

    const locationsOnDays = ref([])

    const setLocationMarkers = locations => locationsOnDays.value = locations

    const selectedMoveUser = ref(null)
    /** Note this is a get/set computed property so we can set it to null when the modal is closed **/
    const showMoveUserModal = computed({
        get: () => !!selectedMoveUser.value,
        set: value => selectedMoveUser.value = value ? selectedMoveUser.value : null,
    })

    const promptMoveVolunteer = (selection, volunteer, shift) => selectedMoveUser.value = {selection, volunteer, shift}

    const moveVolunteer = async (volunteerId, locationId, shiftId) => {
        selectedMoveUser.value = null
        try {
            await axios.put('/admin/move-volunteer-to-shift', {
                user_id: volunteerId,
                location_id: locationId,
                old_shift_id: shiftId,
                date: format(date.value, 'yyyy-MM-dd'),
            })
            toast.success('User was moved!')
        } catch (e) {
            toast.error(e.response.data.message)

        } finally {
            await getShifts()
        }
    }

    const assignVolunteer = async ({volunteerId, volunteerName, location, shift}) => {
        try {
            await axios.put('/admin/toggle-shift-for-user', {
                do_reserve: true,
                user: volunteerId,
                location: location.id,
                shift: shift.id,
                date: format(date.value, 'yyyy-MM-dd'),
            })
            toast.success(`${volunteerName} was assigned to ${location.name} at ${shift.start_time}`)
        } catch (e) {
            toast.error(e.response.data.message)

        } finally {
            await getShifts()
        }
    }

    const removeVolunteer = async () => {
        try {
            await axios.delete('/admin/toggle-shift-for-user', {
                data: {
                    do_reserve: false,
                    user: selectedRemoveUser.value.volunteer.id,
                    location: selectedRemoveUser.value.location.id,
                    shift: selectedRemoveUser.value.shift.id,
                    date: format(selectedRemoveUser.value.date, 'yyyy-MM-dd'),
                }
            })
            toast.warning(`${selectedRemoveUser.value.volunteer.name} was removed from ${selectedRemoveUser.value.location.name} at ${selectedRemoveUser.value.shift.start_time}`)
            selectedRemoveUser.value = null
        } catch (e) {
            toast.error(e.response.data.message)

        } finally {
            await getShifts()
        }
    }

    const today = new Date()
    const formatTime = time => format(parse(time, 'HH:mm:ss', today), 'h:mm a')

    const showUserAddModal = ref(false)

    const rowClass = gender => {
        if (gender === 'male') {
            return 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60'
        } else if (gender === 'female') {
            return 'bg-pink-100 hover:bg-pink-200 dark:bg-fuchsia-900/40 dark:hover:bg-fuchsia-900/60'
        }
        return 'bg-slate-200 dark:bg-slate-700 dark:text-gray-50'
    };


    const assignUserData = reactive({
        shift: null,
        location: null,
    })

    const doShowAssignVolunteerModal = (shift, location) => {
        assignUserData.shift = shift
        assignUserData.location = location
        showUserAddModal.value = true
    }

    const selectedRemoveUser = ref(null)

    const setRemoveUser = (volunteer, shift, location, date) =>
        selectedRemoveUser.value = {volunteer, shift, location, date}

    /** Note this is a get/set computed property so we can set it to null when the modal is closed **/
    const showRemoveVolunteerModal = computed({
        get: () => !!selectedRemoveUser.value,
        set: value => selectedRemoveUser.value = value ? selectedRemoveUser.value : null,
    })

    const removeTooltip = name => `Remove ${name} from this shift`
</script>

<template>
    <div class="grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto]">
        <div class="pb-3 md:pb-0">
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
                                {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
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
                            <div
                                class="col-span-full dark:text-gray-50">
                                <div v-for="(volunteer, index) in shift.filterVolunteers"
                                     :key="index"
                                     class="border-b border-gray-400 last:border-b-0 first:rounded-t-md last:rounded-b-md p-2 transition duration-150 hover:ease-in"
                                     :class="rowClass(volunteer?.gender)">
                                    <div v-if="volunteer" class="grid grid-cols-2 gap-1.5">
                                        <div class="md:mr-3">
                                            {{ volunteer.gender === 'male' ? 'Bro' : 'Sis' }}
                                            {{ volunteer.name }}
                                        </div>
                                        <div class="text-right">Ph: <a :href="`tel:${volunteer.mobile_phone}`"
                                                                       class="underline underline-offset-4 decoration-dotted decoration-1 decoration-blue-800 visited:decoration-blue-800">{{
                                                volunteer.mobile_phone
                                            }}</a>
                                        </div>
                                        <div class="col-span-2 grid grid-cols-2 gap-1.5 lg:flex lg:gap-3">
                                        <MoveUserSelectField class="inline-block"
                                                             :volunteer="volunteer"
                                                             :date="date"
                                                             :shift="shift"
                                                             :location-id="location.id"
                                                             :empty-shifts-for-time="emptyShiftsForTime"
                                                             @update:modelValue="promptMoveVolunteer($event, volunteer, shift)"/>
                                        <div class="text-right">
                                            <JetButton style-type="danger"
                                                       v-tooltip="removeTooltip(volunteer.name)"
                                                       @click="setRemoveUser(volunteer, shift, location, date)">
                                                <UserRemove color="#000"/>
                                            </JetButton>
                                        </div>
                                        </div>
                                    </div>

                                    <div v-else>
                                        <JetButton style-type="info"
                                                   @click="doShowAssignVolunteerModal(shift, location)">
                                            <UserAdd color="#fff"/>
                                            <span class="ml-3">Add Volunteer</span>
                                        </JetButton>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </Accordion>
        </div>
    </div>
    <UserActionsModal
        v-model:show="showUserAddModal"
        :date="date"
        :shift="assignUserData.shift"
        :location="assignUserData.location"
        @assignVolunteer="assignVolunteer"/>

    <JetConfirmModal v-model:show="showMoveUserModal">
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
                <JetButton
                    @click="moveVolunteer(selectedMoveUser?.volunteer.id, selectedMoveUser?.selection.id, selectedMoveUser?.shift.id)"
                    class="ml-2">
                    Move
                </JetButton>
            </div>
        </template>
    </JetConfirmModal>

    <JetConfirmModal v-model:show="showRemoveVolunteerModal">
        <template #title>
            <h2 class="text-lg font-medium text-gray-900">Remove user</h2>
        </template>
        <template #content>
            <div>
                Are you sure you want to remove {{ selectedRemoveUser.volunteer.name }} from
                {{ selectedRemoveUser.location.name }}?
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <JetButton style-type="secondary" @click="selectedRemoveUser = null">Cancel</JetButton>
                <JetButton
                    style-type="warning"
                    @click="removeVolunteer()"
                    class="ml-2">
                    Remove
                </JetButton>
            </div>
        </template>
    </JetConfirmModal>
</template>
