<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    // import RadioDropDown from '@/Components/RadioDropDown.vue'
    import Shift from '@/Pages/Admin/Locations/Partials/Shift.vue'
    //https://vue3datepicker.com/
    import { computed, defineProps } from 'vue'

    const props = defineProps({
        modelValue: Object,
    })

    const emit = defineEmits([
        'update:modelValue',
    ])

    const form = computed({
        get () {
            return props.modelValue
        },
        set (value) {
            emit('update:modelValue', value)
        },
    })

    const days = [
        { label: 'Mo', value: 'day_monday' },
        { label: 'Tu', value: 'day_tuesday' },
        { label: 'We', value: 'day_wednesday' },
        { label: 'Th', value: 'day_thursday' },
        { label: 'Fr', value: 'day_friday' },
        { label: 'Sa', value: 'day_saturday' },
        { label: 'Su', value: 'day_sunday' },
    ]

    const addShift = () => {
        form.value.shifts.unshift({
            location_id: form.value.id,
            day_monday: false,
            day_tuesday: false,
            day_wednesday: false,
            day_thursday: false,
            day_friday: false,
            day_saturday: false,
            day_sunday: false,
            start_time: '00:00:00',
            end_time: '12:00:00',
            available_from: null,
            available_to: null,
        })
    }
</script>

<template>
    <div class="col-span-full flex justify-end">
        <JetButton type="button" style-type="info" @click="addShift">Add New Shift</JetButton>
    </div>

    <div class="col-span-full grid grid-cols-[auto_1fr_1fr] gap-4">
        <template v-for="(shift, index) in form.shifts" :key="shift.id">
            <Shift v-model="form.shifts[index]" :index="index" :days="days" :errors="form.errors"/>
        </template>
    </div>
</template>
