<script setup>
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { addDays, addMonths, endOfDay, endOfMonth, formatISO, isAfter, isBefore, parseISO, startOfDay, startOfMonth, subMonths } from 'date-fns'
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
            emit('update:date', value)
        },
    })

    const today = new Date()
    const notBefore = props.canViewHistorical
        ? startOfDay(startOfMonth(subMonths(today, 6)))
        : startOfDay(today)
    const notAfter = props.canViewHistorical
        ? endOfMonth(addMonths(notBefore, 7))
        : endOfMonth(addMonths(notBefore, 1))

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
            let foundAtLocation = 0
            const shiftDateGroup = props.markerDates[date]

            const isoDate = parseISO(date)

            for (const shiftId in shiftDateGroup) {
                if (!shiftDateGroup.hasOwnProperty(shiftId)) {
                    continue
                }

                const shifts = shiftDateGroup[shiftId]
                let maxVolunteers = 0
                let volunteerCount = 0
                for (let i = 0; i < shifts.length; i++) {
                    const shift = shifts[i]
                    if (isBefore(isoDate, startOfDay(parseISO(shift.available_from)))) {
                        break
                    }
                    if (isAfter(isoDate, endOfDay(parseISO(shift.available_to)))) {
                        break
                    }
                    if (i === 0) {
                        maxVolunteers = shift.max_volunteers
                    }
                    volunteerCount++
                    if (shift.volunteer_id === props.user?.id) {
                        foundAtLocation = shift.location_id
                    }
                }
                if (volunteerCount < maxVolunteers) {
                    freeShiftCount++
                }
            }
            if (freeShiftCount > 0) {
                highlighted.push(isoDate)
            }
            if (foundAtLocation) {
                marks.push({
                    date: isoDate,
                    type: 'line',
                    color: '#0E9F6E',
                    tooltip: [{ text: `You have a shift`, color: '#0E9F6E' }],
                    location: foundAtLocation,
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

        emit('locations-for-day', marks.map(marker => ({ location: marker.location, date: marker.date })))
    })
</script>
<template>
    <Datepicker inline
                auto-apply
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
