<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import BookedSlot from '@/Components/Icons/BookedSlot.vue'
    import EmptySlot from '@/Components/Icons/EmptySlot.vue'
    import Female from '@/Components/Icons/Female.vue'
    import Male from '@/Components/Icons/Male.vue'
    import useToast from '@/Composables/useToast'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    // noinspection ES6UnusedImports
    import { VTooltip } from 'floating-vue'
    // import 'floating-vue/dist/style.css'
    // import { useToast } from 'vue-toastification'

    defineProps({
        user: Object,
    })

    const toast = useToast()

    const { date, locations, getShifts } = useLocationFilter()

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
</script>

<template>
    <div class="p-3 grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto]">
        <div class="bg-white pb-3 justify-self-center">
            <Datepicker inline auto-apply :enable-time-picker="false" v-model="date">
                <template #day="{day, date}">
                    <pre class="text-sm">{{ day }}</pre>
                </template>
            </Datepicker>
        </div>

        <div class="text-sm">
            <Accordion :items="locations" label="name" uid="id">
                <template v-slot="{location}">
                    <div class="w-full grid gap-x-2 gap-y-4" :class="gridCols[location.max_volunteers]">
                        <template v-for="shift in location.filterShifts" :key="shift.id">
                            <div class="self-center pl-3">{{ shift.start_time }} - {{ shift.end_time }}</div>
                            <div v-for="(volunteer, index) in shift.filterVolunteers"
                                 :key="index"
                                 class="self-center justify-self-center">
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
                                <button v-else
                                        type="button"
                                        class="block"
                                        @click="toggleReservation(location.id, shift.id, true)">
                                    <EmptySlot v-tooltip="'Tap to reserve this shift'"/>
                                </button>
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


            <!--            <pre>{{ locations }}</pre>-->
            <!--                        <ul>-->
            <!--                            <li v-for="location in locations" class="mb-6">-->
            <!--                                <div class="font-bold">{{ location.name }}</div>-->
            <!--                                <ul class="pl-6">-->
            <!--                                    <li v-for="shift in location.filterShifts" class="list-disc mb-2">-->
            <!--                                        <div>{{ shift.start_time }} - {{ shift.end_time }}</div>-->
            <!--                                        <ul class="pl-6">-->
            <!--                                            <li v-for="volunteer in shift.filterVolunteers" class="list-disc">-->
            <!--                                                <div>{{ volunteer.name }}</div>-->
            <!--                                            </li>-->
            <!--                                        </ul>-->
            <!--                                    </li>-->
            <!--                                </ul>-->
            <!--                            </li>-->
            <!--                        </ul>-->
        </div>
    </div>
</template>
