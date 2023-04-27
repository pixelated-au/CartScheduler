<script setup>
    import SelectField from '@/Components/SelectField.vue'
    import {addMinutes, areIntervalsOverlapping, format, parse, subMinutes} from 'date-fns'
    import {computed, inject} from 'vue'
    import UserMove from "@/Components/Icons/UserMove.vue";

    const props = defineProps({
        date: Date,
        shift: Object,
        locationId: Number,
        emptyShiftsForTime: Array,
    })
    const emit = defineEmits(['update:modelValue'])

    const shiftStart = computed(() => parse(props.shift.start_time, 'HH:mm:ss', props.date))

    const hasMatch = (shiftData) => {
        return areIntervalsOverlapping(
            {start: subMinutes(shiftStart.value, 45), end: addMinutes(shiftStart.value, 45)},
            {start: shiftData.startTime, end: addMinutes(shiftData.startTime, 30)},
        ) && shiftData.locationId !== props.locationId
    }

    const hasShiftsForTime = computed(() => {
        return !!props.emptyShiftsForTime?.find(shiftData => hasMatch(shiftData))
    })

    const shiftsForTime = computed(() => {
        return props.emptyShiftsForTime
            ?.filter(shiftData => hasMatch(shiftData))
            ?.map(({location, locationId, currentVolunteers, startTime, endTime}) => {
                const label = `${location}: ${format(startTime, 'h:mm a')} - ${format(endTime, 'h:mm a')}`

                const volunteers = currentVolunteers.map(volunteer => {
                    const prefix = volunteer.gender === 'male' ? 'Bro' : 'Sis'
                    return `${prefix} ${volunteer.name}`
                })
                return {label, volunteers, id: locationId}
            })
    })
    const isDarkMode = inject('darkMode', false)
</script>
<template>
    <SelectField v-if="hasShiftsForTime"
                 @update:modelValue="$emit('update:modelValue', $event)"
                 :options="shiftsForTime">
        <template #label="{option}">
            <UserMove color="#fff"/>
        </template>
        <template #extra="{option}">
            <ul class="pl-3 font-normal">
                <li v-for="volunteer in option.volunteers" :key="volunteer" class="list-disc">
                    {{ volunteer }}
                </li>
            </ul>
        </template>
    </SelectField>
    <span v-else class="text-red-500">No shifts available</span>
</template>
