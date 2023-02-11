<script setup>
    import { usePage } from '@inertiajs/inertia-vue3'

    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { addDays, addMonths, endOfDay, endOfMonth, formatISO, isAfter, isBefore, parse, parseISO, startOfDay, startOfMonth, subMonths } from 'date-fns'
    import { computed, defineEmits, defineProps, inject, onMounted, ref, watchEffect } from 'vue'

    const props = defineProps({
        date: Date,
        locations: Array,
        markerDates: Object,
        user: Object,
        canViewHistorical: Boolean,
    })

    const isDarkMode = inject('darkMode', false)

    const emit = defineEmits([
        'update:date',
        'locations-for-day',
    ])

    const selectedDate = computed({
        get () {
            return props.date
        },
        set (value) {
            emit('update:date', parse('12:00:00', 'HH:mm:ss', value))
        },
    })

    const foo = computed(() => {
        console.log(usePage().props.value.shiftAvailability)
        return usePage().props.value.shiftAvailability
    })

    console.log(foo.value)


    const today = new Date()
    const notBefore = props.canViewHistorical
        ? startOfDay(startOfMonth(subMonths(today, 6)))
        : startOfDay(today)
    const notAfter = props.canViewHistorical
        ? endOfMonth(addMonths(notBefore, 12))
        : parseISO(foo.value.maxDateReservation)

    const allDates = []
    const getAllDates = () => {
        for (let i = notBefore; i < notAfter; i = addDays(i, 1)) {
            allDates.push(i)
        }
    }

    onMounted(() => {
        getAllDates()
    })

    const markers = ref([])
    const highlights = ref([])

    watchEffect(() => {
        if (!props.markerDates) {
            return
        }
        const marks = []
        const highlighted = []
        if (!props.user) {
            return []
        }

        for (const date in props.markerDates) {
            if (!props.markerDates.hasOwnProperty(date)) {
                continue
            }
            let freeShiftCount = 0
            const foundAtLocation = []
            const shiftDateGroup = props.markerDates[date]

            const isoDate = parseISO(date)

            for (const shiftId in shiftDateGroup) {
                if (!shiftDateGroup.hasOwnProperty(shiftId)) {
                    continue
                }

                const shifts = shiftDateGroup[shiftId]
                let maxVolunteers = 0
                let volunteerCount = 0
                for (let shiftCount = 0; shiftCount < shifts.length; shiftCount++) {
                    const shift = shifts[shiftCount]
                    if (isBefore(isoDate, startOfDay(parseISO(shift.available_from)))) {
                        break
                    }
                    if (isAfter(isoDate, endOfDay(parseISO(shift.available_to)))) {
                        break
                    }
                    if (shiftCount === 0) {
                        // This is set on the first iteration of the loop
                        maxVolunteers = shift.max_volunteers
                    }
                    volunteerCount++
                    if (shift.volunteer_id === props.user?.id) {
                        foundAtLocation.push(shift.location_id)
                    }
                }
                if (volunteerCount < maxVolunteers) {
                    freeShiftCount++
                }
            }
            if (freeShiftCount > 0) {
                highlighted.push(isoDate)
            }
            if (foundAtLocation.length) {
                marks.push({
                    date: isoDate,
                    type: 'line',
                    color: '#0E9F6E',
                    tooltip: [{ text: `You have a shift`, color: '#0E9F6E' }],
                    locations: foundAtLocation,
                })
            }
        }

        for (const allDate of allDates) {
            // fill in the rest of the empty dates
            let found = false
            for (const markerDatesKey in props.markerDates) {
                if (markerDatesKey === formatISO(allDate, { representation: 'date' })) {
                    found = true
                    break
                }
            }
            if (!found) {
                highlighted.push(allDate)
            }
        }

        markers.value = marks
        highlights.value = highlighted

        emit('locations-for-day', marks.map(marker => ({ locations: marker.locations, date: marker.date })))
    })
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
                :min-date="notBefore"
                :max-date="notAfter"
                :dark="isDarkMode">
        <template #day="{day, date}">
            <pre class="text-sm">{{ day }}</pre>
        </template>
    </Datepicker>

</template>
