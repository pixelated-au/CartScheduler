<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, nextTick, reactive, watch, inject } from "vue";
import useAvailabilityActions from "@/Composables/useAvailabilityActions";
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import DayOfWeekConfiguration from "@/Pages/Profile/Partials/DayOfWeekConfiguration.vue";

const props = defineProps({
  availability: {
    type: Object,
    required: true,
  },
  userId: {
    type: Number,
    default: null,
  },
});

const route = inject("route");

const ranges = computed(() => ({
  start: usePage().props.shiftAvailability.systemShiftStartHour,
  end: usePage().props.shiftAvailability.systemShiftEndHour,
}));

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
  comments: props.availability.comments || "",
});

const { computedRange, numberOfWeeks, toggleRosterDay, tooltipFormat } = useAvailabilityActions(form, ranges);

const hasDayError = computed(() => {
  return form.errors["day_monday"]?.length > 0
    || form.errors["day_tuesday"]?.length > 0
    || form.errors["day_wednesday"]?.length > 0
    || form.errors["day_thursday"]?.length > 0
    || form.errors["day_friday"]?.length > 0
    || form.errors["day_saturday"]?.length > 0
    || form.errors["day_sunday"]?.length > 0;
});

const hasNumError = computed(() => {
  return form.errors["num_mondays"]?.length > 0
    || form.errors["num_tuesdays"]?.length > 0
    || form.errors["num_wednesdays"]?.length > 0
    || form.errors["num_thursdays"]?.length > 0
    || form.errors["num_fridays"]?.length > 0
    || form.errors["num_saturdays"]?.length > 0
    || form.errors["num_sundays"]?.length > 0;
});

const rosters = reactive([
  { label:"Monday", value: toggleRosterDay("monday") },
  { label:"Tuesday", value: toggleRosterDay("tuesday") },
  { label:"Wednesday", value: toggleRosterDay("wednesday") },
  { label:"Thursday", value: toggleRosterDay("thursday") },
  { label:"Friday", value: toggleRosterDay("friday") },
  { label:"Saturday", value: toggleRosterDay("saturday") },
  { label:"Sunday", value: toggleRosterDay("sunday") },
]);

const rosterMonday = toggleRosterDay("monday");
const rosterTuesday = toggleRosterDay("tuesday");
const rosterWednesday = toggleRosterDay("wednesday");
const rosterThursday = toggleRosterDay("thursday");
const rosterFriday = toggleRosterDay("friday");
const rosterSaturday = toggleRosterDay("saturday");
const rosterSunday = toggleRosterDay("sunday");

const hoursEachDay = reactive({
  monday: computedRange("monday"),
  tuesday: computedRange("tuesday"),
  wednesday: computedRange("wednesday"),
  thursday: computedRange("thursday"),
  friday: computedRange("friday"),
  saturday: computedRange("saturday"),
  sunday: computedRange("sunday"),
});

const update = () => {
  form.transform((data) => {
    if (props.userId) {
      return {
        ...data,
        user_id: props.userId,
      };
    }
    return data;
  })
    .put(route("update.user.availability"), {
      preserveScroll: true,
      onError: () => {
        console.log("error");
      },
    });
};

const showConfigurations = computed(() => {
  return form.num_mondays > 0 || form.num_tuesdays > 0 || form.num_wednesdays > 0 || form.num_thursdays > 0 || form.num_fridays > 0 || form.num_saturdays > 0 || form.num_sundays > 0;
});

const maxCommentChars = 500;
const commentsRemainingCharacters = computed(() => {
  if (form.comments) {
    return maxCommentChars - form.comments.length;
  }
  return maxCommentChars;
});

watch(() => form.comments, (value, oldValue) => {
  if (value.length > maxCommentChars) {
    nextTick(() => {
      form.comments = oldValue;
    });
  }
});
</script>

<template>
  <JetFormSection @submitted="update">
    <template #title>
      Regular Availability
    </template>

    <template #description>
      Please indicate {{ props.userId ? "this volunteers" : "your" }} availability
    </template>

    <template #form>
      <div
          class="col-span-6 grid grid-cols-4 lg:grid-cols-7 text-gray-700 dark:text-gray-100 items-stretch gap-y-px  border border-gray-200 dark:border-gray-900 rounded p-3 bg-sub-panel dark:bg-sub-panel-dark">
        <div class="col-span-4 lg:col-span-7 font-bold">
          {{ props.userId ? "Volunteer is" : "I am" }} available
          to be rostered:
        </div>
        <div v-for="roster in rosters" :key="roster.label" class="col text-center flex flex-col justify-center items-center">
          <div class="text-sm">
            {{ roster.label }}
          </div>
          <PToggleSwitch :id="`check-${roster.label}`" v-model="roster.value" label="Monday" />
        </div>
        <div v-if="hasDayError" class="col-span-4 lg:col-span-7 font-bold">
          <p class="text-sm text-red-600">
            {{ form.errors.day_monday }}
            {{ form.errors.day_tuesday }}
            {{ form.errors.day_wednesday }}
            {{ form.errors.day_thursday }}
            {{ form.errors.day_friday }}
            {{ form.errors.day_saturday }}
            {{ form.errors.day_sunday }}
          </p>
        </div>
      </div>
      <!--      <Transition -->
      <!--          enter-from-class="opacity-0" -->
      <!--          enter-active-class="transition-[grid-template-rows] ease-in duration-300" -->
      <!--          enter-to-class="opacity-100" -->
      <!--          leave-from-class="opacity-100" -->
      <!--          leave-active-class="transition-[grid-template-rows] ease-in duration-300" -->
      <!--          leave-to-class="opacity-0"> -->
      <div
          v-show="showConfigurations"
          class="col-span-6 text-gray-700 dark:text-gray-100 grid grid-cols-12 gap-y-8 gap-x-2 lg:gap-x-6 items-center border border-gray-200 dark:border-gray-900 rounded p-3  bg-sub-panel dark:bg-sub-panel-dark">
        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.monday"
            v-model:number-of-days-per-month="form.num_mondays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Monday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.tuesday"
            v-model:number-of-days-per-month="form.num_tuesdays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Tuesday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.wednesday"
            v-model:number-of-days-per-month="form.num_wednesdays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Wednesday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.thursday"
            v-model:number-of-days-per-month="form.num_thursdays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Thursday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.friday"
            v-model:number-of-days-per-month="form.num_fridays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Friday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.saturday"
            v-model:number-of-days-per-month="form.num_saturdays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Saturday"
            :tooltip-format="tooltipFormat" />

        <DayOfWeekConfiguration
            v-model:hours-each-day="hoursEachDay.sunday"
            v-model:number-of-days-per-month="form.num_sundays"
            :start="ranges.start"
            :end="ranges.end"
            :number-of-weeks="numberOfWeeks"
            label="Sunday"
            :tooltip-format="tooltipFormat" />
      </div>
      <!--      </Transition> -->
      <div
          v-if="hasNumError"
          class="col-span-6 text-gray-700 dark:text-gray-100 items-stretch gap-y-5 sm:gap-y-px  border border-gray-200 dark:border-gray-900 rounded p-3">
        <p class="text-sm text-red-600">
          {{ form.errors.num_mondays }}
          {{ form.errors.num_tuesdays }}
          {{ form.errors.num_wednesdays }}
          {{ form.errors.num_thursdays }}
          {{ form.errors.num_fridays }}
          {{ form.errors.num_saturdays }}
          {{ form.errors.num_sundays }}
        </p>
      </div>

      <div
          class="col-span-6 text-gray-700 dark:text-gray-100 items-stretch  border border-gray-200  bg-sub-panel dark:bg-sub-panel-dark dark:border-gray-900 rounded p-3">
        <JetLabel for="availability-comments" value="Comments (optional)" />
        <textarea
            id="availability-comments"
            class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm w-full h-40 bg-transparent dark:border-slate-700 dark:text-white"
            v-model="form.comments" />
        <div class="text-sm">{{ commentsRemainingCharacters }} characters remaining</div>
        <JetInputError :message="form.errors.comments" class="mt-2" />
        <div class="italic, text-gray-500 text-sm">
          If relevant, use this to provide any additional information about your availability that could
          assist in scheduling.
        </div>
      </div>
    </template>

    <template #actions>
      <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
        Saved.
      </JetActionMessage>

      <PButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Save
      </PButton>
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
