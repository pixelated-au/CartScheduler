<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import JetCheckbox from '@/Jetstream/Checkbox.vue'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { computed, defineProps, inject, ref } from 'vue'

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

    const showModal = ref(false)

    const deleteShift = async () => {
        if (shift.value.id) {
            await axios.delete('/admin/shifts/' + shift.value.id)
        }

        emit('delete', props.index)
    }

    const darkMode = inject('darkMode', false)

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
        <div class="sm:col-span-2">
            <JetLabel :for="`shift-range-${fieldUnique}`" value="Shift Time From & To"/>
            <Datepicker time-picker
                        range
                        auto-apply
                        v-model="shiftTimeRange"
                        :id="`shift-range-${fieldUnique}`"
                        :enable-seconds="false"
                        :clearable="false"
                        :minutes-increment="5"
                        :dark="darkMode"/>
            <JetInputError :message="errors[`shifts.${index}.start_time`]" class="mt-2"/>
            <JetInputError :message="errors[`shifts.${index}.end_time`]" class="mt-2"/>
        </div>
        <div>
            <JetLabel :for="`is-enabled-${fieldUnique}`" value="Enabled?"/>
            <JetCheckbox :id="`is-enabled-${fieldUnique}`" v-model:checked="shift.is_enabled" class="mt-3"/>
        </div>
        <div class="sm:col-start-2">
            <JetLabel :for="`available-from-${fieldUnique}`" value="Available From"/>
            <Datepicker auto-apply
                        enable-time-picker
                        format="dd/MM/yyyy"
                        v-model="shift.available_from"
                        :id="`available-from-${fieldUnique}`"
                        :close-on-auto-apply="false"
                        :enable-seconds="false"
                        :enable-time-picker="false"
                        :minutes-grid-increment="5"
                        :dark="darkMode"/>
            <div class="text-xs text-gray-500">Optional</div>
            <JetInputError :message="errors[`shifts.${index}.available_from`]" class="mt-2"/>

        </div>
        <div class="">
            <JetLabel :for="`available-to-${fieldUnique}`" value="Available To"/>
            <Datepicker auto-apply
                        enable-time-picker
                        format="dd/MM/yyyy"
                        v-model="shift.available_to"
                        :id="`available-to-${fieldUnique}`"
                        :enable-seconds="false"
                        :enable-time-picker="false"
                        :minutes-grid-increment="5"
                        :dark="darkMode"/>
            <div class="text-xs text-gray-500">Optional</div>
            <JetInputError :message="errors[`shifts.${index}.available_to`]" class="mt-2"/>
        </div>
        <div class="self-end">
            <JetButton type="button" style-type="warning" outline @click="showModal = true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                    <path fill="none" d="M0 0h24v24H0z"/>
                    <path class="fill-red-600"
                          d="M4 8h16v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8zm2 2v10h12V10H6zm3 2h2v6H9v-6zm4 0h2v6h-2v-6zM7 5V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2h5v2H2V5h5zm2-1v1h6V4H9z"/>
                </svg>
            </JetButton>
        </div>
        <JetSectionBorder class="col-span-full"/>
    </template>


    <JetConfirmationModal :show="showModal" :closeable="false">
        <template #title>DANGER!</template>
        <template #content>
            <p>Are you sure you want to delete this shift?</p>
        </template>
        <template #footer>
            <JetButton type="button" style-type="secondary" @click="showModal = false" class="mr-3">Cancel</JetButton>
            <JetButton type="button" style-type="warning" outline @click="deleteShift">Delete</JetButton>
        </template>
    </JetConfirmationModal>
</template>

