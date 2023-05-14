<script setup>
    import SelectField from '@/Components/SelectField.vue'
    import {addMinutes, areIntervalsOverlapping, format, getDay, parse, subMinutes} from 'date-fns'
    import {computed, inject} from 'vue'
    import UserMove from "@/Components/Icons/UserMove.vue";
    // noinspection ES6UnusedImports
    import {VTooltip} from 'floating-vue'

    const props = defineProps({
        volunteer: Object,
        date: Date,
        shift: Object,
        locationId: Number,
        emptyShiftsForTime: Array,
    })
    const emit = defineEmits(['update:modelValue'])

    const shiftStart = computed(() => parse(props.shift.start_time, 'HH:mm:ss', props.date))

    const dayOfWeek = computed(() => getDay(props.date))

    const hasMatch = (shiftData) => {
        return areIntervalsOverlapping(
                {start: subMinutes(shiftStart.value, 45), end: addMinutes(shiftStart.value, 45)},
                {start: shiftData.startTime, end: addMinutes(shiftData.startTime, 30)},
            )
            && shiftData.locationId !== props.locationId
            && shiftData.days[dayOfWeek.value] === true
            && (
                (!shiftData.available_from || shiftData.available_from >= props.date)
                &&
                (!shiftData.available_to || shiftData.available_to <= props.date)
            )
    }

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
    const iconColor = computed(() => {
        if (shiftsForTime.value?.length === 0) {
            return isDarkMode.value ? '#fff' : '#000'
        }
        return '#fff'
    })
    const moveTooltip = computed(() => shiftsForTime.value?.length === 0
        ? `No other locations available`
        : `Move ${props.volunteer.name} to another shift`)

</script>
<template>
    <SelectField :options="shiftsForTime"
                 v-tooltip="moveTooltip"
                 emptyNote="No other locations available"
                 @update:modelValue="$emit('update:modelValue', $event)">
        <template #label="{option}">
            <UserMove :color="iconColor"/>
        </template>
        <template #extra="{option}">
            <ul class="pl-3 font-normal">
                <li v-for="volunteer in option.volunteers" :key="volunteer" class="list-disc">
                    {{ volunteer }}
                </li>
            </ul>
        </template>
    </SelectField>
</template>
