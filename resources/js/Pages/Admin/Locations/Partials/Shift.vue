<script setup>
    import JetCheckbox from '@/Jetstream/Checkbox.vue'
    // import RadioDropDown from '@/Components/RadioDropDown.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { computed, defineProps } from 'vue'

    const props = defineProps({
        modelValue: Object,
        days: Array,
        index: Number,
        errors: Object,
    })

    const emit = defineEmits([
        'update:modelValue',
    ])

    const shift = computed({
        get: () => props.modelValue,
        set: value => {
            emit('update:modelValue', value)
        },
    })

    const prefixTime = time => {
        if (time < 10) {
            return `0${time}`
        }
        return '' + time
    }

    const shiftTimeRange = computed({
        get: () =>
            [
                {
                    hours: parseInt(shift.value.start_time?.substring(0, 2)) || 0,
                    minutes: parseInt(shift.value.start_time?.substring(3, 5)) || 0,
                },
                {
                    hours: parseInt(shift.value.end_time?.substring(0, 2)) || 0,
                    minutes: parseInt(shift.value.end_time?.substring(3, 5)) || 0,
                },
            ],
        set: value => {
            shift.value.start_time = prefixTime(value[0].hours) + ':' + prefixTime(value[0].minutes) + ':00'
            shift.value.end_time = prefixTime(value[1].hours) + ':' + prefixTime(value[1].minutes) + ':00'
        },
    })

    const fieldUnique = computed(() => shift.value.id || Math.random().toString(36).substring(2, 9))
</script>

<template>
    <template v-if="shift">
        <div class="">
            <div class="grid grid-cols-7 gap-2">
                <div v-for="day in days" :key="day.label" class="text-center justify-self-center">
                    <JetLabel :for="day.value + fieldUnique" :value="day.label"/>
                    <JetCheckbox :id="day.value + fieldUnique"
                                 v-model:checked="shift[day.value]"
                                 :value="day.value"
                                 class="mt-3"/>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <JetLabel :for="`shift-range-${fieldUnique}`" value="Shift Time From & To"/>
            <Datepicker time-picker
                        range
                        auto-apply
                        v-model="shiftTimeRange"
                        :name="`shift-range-${fieldUnique}`"
                        :enable-seconds="false"
                        :clearable="false"
                        :minutes-increment="5"/>
            <JetInputError :message="errors[`shifts.${index}.start_time`]" class="mt-2"/>
            <JetInputError :message="errors[`shifts.${index}.end_time`]" class="mt-2"/>
        </div>
        <div class="col-start-2">
            <JetLabel :for="`available-from-${fieldUnique}`" value="Shift Available From"/>
            <Datepicker auto-apply
                        enable-time-picker
                        v-model="shift.available_from"
                        :name="`available-from-${fieldUnique}`"
                        :close-on-auto-apply="false"
                        :enable-seconds="false"
                        :minutes-grid-increment="5"/>
            <JetInputError :message="errors[`shifts.${index}.available_from`]" class="mt-2"/>

        </div>
        <div class="">
            <JetLabel :for="`available-to-${fieldUnique}`" value="Available To"/>
            <Datepicker auto-apply
                        enable-time-picker
                        v-model="shift.available_to"
                        :name="`available-to-${fieldUnique}`"
                        :enable-seconds="false"
                        :minutes-grid-increment="5"/>
            <JetInputError :message="errors[`shifts.${index}.available_to`]" class="mt-2"/>
        </div>
        <JetSectionBorder class="col-span-full"/>
    </template>
</template>

