<script setup>
    import JetCheckbox from '@/Jetstream/Checkbox.vue'
    // import RadioDropDown from '@/Components/RadioDropDown.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
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
</script>

<template>

    <div class="grid grid-cols-[auto_1fr_1fr] gap-4">
        <template v-for="(shift, index) in form.shifts" :key="shift.id">
            <div class="">
                <div class="grid grid-cols-7 gap-2">
                    <div v-for="day in days" :key="day.label" class="text-center justify-self-center">
                        <JetLabel :for="day.value + shift.id" :value="day.label"/>
                        <JetCheckbox :id="day.value + shift.id"
                                     v-model:checked="shift[day.value]"
                                     :value="day.value"
                                     class="mt-3"/>
                    </div>
                </div>
            </div>
            <div class="">
                <JetLabel :for="`shift-start-${shift.id}`" value="Shift Start"/>
                <JetInput :id="`shift-start-${shift.id}`"
                          v-model="shift.start_time"
                          type="time"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors[`shifts.${index}.start_time`]" class="mt-2"/>
            </div>
            <div class="">
                <JetLabel :for="`shift-end-${shift.id}`" value="Shift End"/>
                <JetInput :id="`shift-end-${shift.id}`" v-model="shift.end_time" type="time" class="mt-1 block w-full"/>
                <JetInputError :message="form.errors[`shifts.${index}.end_time`]" class="mt-2"/>
            </div>
        </template>
    </div>
</template>
