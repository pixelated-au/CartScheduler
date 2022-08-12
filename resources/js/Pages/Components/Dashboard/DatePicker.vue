<script setup>
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { parseISO } from 'date-fns'
    import { computed, defineEmits, defineProps, ref, watchEffect } from 'vue'

    const props = defineProps({
        date: Date,
        locations: Array,
        markerDates: Object,
        user: Object,
    })

    const emit = defineEmits([
        'update:date',
    ])

    const selectedDate = computed({
        get () {
            return props.date
        },
        set (value) {
            emit('update:date', value)
        },
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
            let foundMe = false
            const shiftDateGroup = props.markerDates[date]

            for (const shiftId in shiftDateGroup) {
                if (!shiftDateGroup.hasOwnProperty(shiftId)) {
                    continue
                }

                const shifts = shiftDateGroup[shiftId]
                let maxVolunteers = 0
                let volunteerCount = 0
                for (let i = 0; i < shifts.length; i++) {
                    const shift = shifts[i]
                    if (i === 0) {
                        maxVolunteers = shift.max_volunteers
                    }
                    volunteerCount++
                    if (shift.volunteer_id === props.user.id) {
                        foundMe = true
                    }
                }
                if (volunteerCount < maxVolunteers) {
                    freeShiftCount++
                }
            }
            if (freeShiftCount > 0) {
                highlighted.push(parseISO(date))
            }
            if (foundMe) {
                marks.push({
                    date: parseISO(date),
                    type: 'line',
                    color: 'orange',
                    tooltip: [{ text: `You have a shift today`, color: 'orange' }],
                })
            }
        }

        markers.value = marks
        highlights.value = highlighted
    })

</script>
<template>
    <Datepicker inline
                auto-apply
                :enable-time-picker="false"
                v-model="selectedDate"
                :markers="markers"
                :highlight="highlights">
        <template #day="{day, date}">
            <pre class="text-sm">{{ day }}</pre>
        </template>
    </Datepicker>

</template>
