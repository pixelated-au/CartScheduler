<script setup>
import useAvailabilityActions from "@/Composables/useAvailabilityActions";
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetToggle from '@/Jetstream/Toggle.vue';
import DayOfWeekConfiguration from "@/Pages/Profile/Partials/DayOfWeekConfiguration.vue";
import {useForm, usePage} from '@inertiajs/inertia-vue3';
import '@vueform/slider/themes/tailwind.scss';
import {computed, reactive} from 'vue';

const props = defineProps({
  availability: {
    type: Object,
    required: true,
  },
})

const ranges = computed(() => ({
  start: usePage().props.value.shiftAvailability.systemShiftStartHour,
  end: usePage().props.value.shiftAvailability.systemShiftEndHour,
}))

const form = useForm({
  day_monday: props.availability.day_monday || [ranges.value.start, ranges.value.end],
  day_tuesday: props.availability.day_tuesday || [ranges.value.start, ranges.value.end],
  day_wednesday: props.availability.day_wednesday || [ranges.value.start, ranges.value.end],
  day_thursday: props.availability.day_thursday || [ranges.value.start, ranges.value.end],
  day_friday: props.availability.day_friday || [ranges.value.start, ranges.value.end],
  day_saturday: props.availability.day_saturday || [ranges.value.start, ranges.value.end],
  day_sunday: props.availability.day_sunday || [ranges.value.start, ranges.value.end],
  num_mondays: props.availability.num_mondays || 0,
  num_tuesdays: props.availability.num_tuesdays || 0,
  num_wednesdays: props.availability.num_wednesdays || 0,
  num_thursdays: props.availability.num_thursdays || 0,
  num_fridays: props.availability.num_fridays || 0,
  num_saturdays: props.availability.num_saturdays || 0,
  num_sundays: props.availability.num_sundays || 0,
})

const {computedRange, numberOfWeeks, toggleRosterDay, tooltipFormat} = useAvailabilityActions(form, ranges)

const rosterMonday = toggleRosterDay('monday')
const rosterTuesday = toggleRosterDay('tuesday')
const rosterWednesday = toggleRosterDay('wednesday')
const rosterThursday = toggleRosterDay('thursday')
const rosterFriday = toggleRosterDay('friday')
const rosterSaturday = toggleRosterDay('saturday')
const rosterSunday = toggleRosterDay('sunday')

const mondayHours = computedRange('monday')

const hoursEachDay = reactive({
  monday: computedRange('monday'),
  tuesday: computedRange('tuesday'),
  wednesday: computedRange('wednesday'),
  thursday: computedRange('thursday'),
  friday: computedRange('friday'),
  saturday: computedRange('saturday'),
  sunday: computedRange('sunday'),
})

const update = () => {
  form.put(route('update.user.availability'), {
    errorBag: 'updatePassword',
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
    },
  })
}

const showConfigurations = computed(() => {
  return form.num_mondays > 0 || form.num_tuesdays > 0 || form.num_wednesdays > 0 || form.num_thursdays > 0 || form.num_fridays > 0 || form.num_saturdays > 0 || form.num_sundays > 0
})
</script>

<template>
    <JetFormSection @submitted="update">
        <template #title>
            Regular Availability
        </template>

        <template #description>
            Please indicate your availability
        </template>

        <template #form>
            <div class="col-span-6 grid grid-cols-7 text-gray-700 dark:text-gray-100 items-stretch gap-y-px bg-slate-200 dark:bg-slate-800 border border-gray-200 dark:border-gray-900 rounded p-3">
                <div class="col-span-7 font-bold">I am available to be rostered:</div>
                <div class="col text-center">
                    <JetToggle id="check-monday" v-model="rosterMonday" label="Monday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-tuesday" v-model="rosterTuesday" label="Tuesday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-wednesday" v-model="rosterWednesday" label="Wednesday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-thursday" v-model="rosterThursday" label="Thursday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-friday" v-model="rosterFriday" label="Friday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-saturday" v-model="rosterSaturday" label="Saturday"/>
                </div>
                <div class="col text-center">
                    <JetToggle id="check-sunday" v-model="rosterSunday" label="Sunday"/>
                </div>
            </div>
            <Transition>
                <div v-show="showConfigurations"
                     class="col-span-6 text-gray-700 dark:text-gray-100 grid grid-cols-2 sm:grid-cols-12 items-stretch gap-y-px bg-slate-200 dark:bg-slate-800 border border-gray-200 dark:border-gray-900 rounded p-3">
                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.monday"
                                            v-model:number-of-days-per-month="form.num_mondays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Monday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.tuesday"
                                            v-model:number-of-days-per-month="form.num_tuesdays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Tuesday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.wednesday"
                                            v-model:number-of-days-per-month="form.num_wednesdays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Wednesday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.thursday"
                                            v-model:number-of-days-per-month="form.num_thursdays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Thursday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.friday"
                                            v-model:number-of-days-per-month="form.num_fridays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Friday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.saturday"
                                            v-model:number-of-days-per-month="form.num_saturdays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Saturday"
                                            :tooltip-format="tooltipFormat"/>

                    <DayOfWeekConfiguration v-model:hours-each-day="hoursEachDay.sunday"
                                            v-model:number-of-days-per-month="form.num_sundays"
                                            :start="ranges.start" :end="ranges.end"
                                            :number-of-weeks="numberOfWeeks" label="Sunday"
                                            :tooltip-format="tooltipFormat"/>
                </div>
            </Transition>
        </template>

        <template #actions>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </JetActionMessage>

            <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </JetButton>
        </template>
    </JetFormSection>
</template>

<style lang="scss" scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
