<script setup>
import { usePage } from "@inertiajs/vue3";
import { format, isSameDay, parse } from "date-fns";
import { computed, onBeforeMount, onMounted, ref, watch } from "vue";
import ComponentSpinner from "@/Components/ComponentSpinner.vue";
import BookedSlot from "@/Components/Icons/BookedSlot.vue";
import EmptySlot from "@/Components/Icons/EmptySlot.vue";
import Female from "@/Components/Icons/Female.vue";
import Male from "@/Components/Icons/Male.vue";
import Loading from "@/Components/Loading.vue";
import useToast from "@/Composables/useToast";
import useLocationFilter from "@/Pages/Admin/Locations/Composables/useLocationFilter";
import DatePicker from "@/Pages/Components/Dashboard/DatePicker.vue";

defineProps({
  user: Object,
});

const toast = useToast();

const timezone = computed(() => usePage().props.shiftAvailability.timezone);

const {
  date,
  freeShifts,
  isLoading,
  locations,
  maxReservationDate,
  serverDates,
  getShifts,
} = useLocationFilter(timezone);

const gridCols = {
  // See tailwind.config.js
  1: "grid-cols-sm-reservation-1 sm:grid-cols-reservation-1",
  2: "grid-cols-sm-reservation-2 sm:grid-cols-reservation-2",
  3: "grid-cols-sm-reservation-3 sm:grid-cols-reservation-3",
  4: "grid-cols-sm-reservation-4 sm:grid-cols-reservation-4",
  5: "grid-cols-sm-reservation-5 sm:grid-cols-reservation-5",
};

const isReserving = ref(false);
const toggleReservation = async (locationId, shiftId, toggleOn) => {
  if (isReserving.value) {
    return;
  }
  const timeoutId = setTimeout(() => isLoading.value = true, 1000);

  try {
    isReserving.value = true;

    const response = await axios.post("/reserve-shift", {
      location: locationId,
      shift: shiftId,
      do_reserve: toggleOn,
      date: format(date.value, "yyyy-MM-dd"),
    });
    if (toggleOn) {
      toast.success(response.data);
    } else {
      toast.warning(response.data);
    }
    await getShifts(false);

  } catch (e) {
    toast.error(e.response.data.message, { timeout: 4000 });
    if (e.response.data.error_code === 100) {
      await getShifts(false);
    }
  } finally {
    isReserving.value = false;
    clearTimeout(timeoutId);
    isLoading.value = false;
  }
};

const locationsOnDays = ref([]);
const flagDates = computed(() =>
  locationsOnDays.value.filter(
    (location) => isSameDay(location.date, date.value),
  ));

const setLocationMarkers = (locations) => locationsOnDays.value = locations;
const isMyShift = (location) => {
  return flagDates.value?.findIndex((d) => d?.locations.includes(location.id)) >= 0;
};

const today = new Date();
const formatTime = (time) => format(parse(time, "HH:mm:ss", today), "h:mm a");

const isRestricted = computed(() => !usePage().props.isUnrestricted);

const firstReservationForUser = ref(undefined);
const locationLabel = ref({});

// Watcher to update locationLabel and firstReservationForUser when dependencies change
watch(
  [locations, flagDates],
  () => {
    firstReservationForUser.value = undefined;
    const labelData = {};
    for (const location of locations.value) {
      const classes = [];
      let tooltip = undefined;
      if (isMyShift(location)) {
        classes.push(...["text-green-800", "dark:text-green-300", "border-b-2", "border-green-500"]);
        tooltip = "You have at least one shift";
        console.log("location", firstReservationForUser.value, location.id, location.name);
        if (firstReservationForUser.value === undefined) {
          firstReservationForUser.value = location.id;
        }
      } else {
        classes.push("dark:text-gray-200");
      }
      labelData[location.id] = { classes, tooltip };
    }
    locationLabel.value = labelData;
  },
);

const accordionExpandIndex = ref(undefined);

watch(firstReservationForUser,(val) => {
  if (val === undefined) {
    accordionExpandIndex.value = undefined;
    return;
  }
  console.log(val);
  accordionExpandIndex.value = val;

});
</script>

<template>
  <div class="p-3 grid gap-3 grid-cols-1 sm:grid-cols-[min-content_auto] sm:items-stretch">
    <div class="pb-3">
      <ComponentSpinner :show="!locations">
        <DatePicker
            v-model:date="date"
            :max-date="maxReservationDate"
            :locations="locations"
            :free-shifts="freeShifts"
            :user="user"
            :marker-dates="serverDates"
            @locations-for-day="setLocationMarkers" />
      </ComponentSpinner>
      <div class="text-sm text-center text-gray-500">Blue squares indicate free shifts</div>
    </div>
    <div>
      <Loading v-if="isLoading" class="min-h-[200px] sm:min-h-full" />
      <PAccordion
          v-model:value="accordionExpandIndex"
          expand-icon="iconify mdi--chevron-down text-2xl ml-auto transition-rotate duration-500 delay-100 ease-in-out"
          collapse-icon="iconify mdi--chevron-down text-2xl rotate-180">
        <!--      <PAccordion v-model:value="accordionExpandIndex" expand-icon="iconify mdi&#45;&#45;chevron-down text-2xl ml-auto transition-rotate duration-500 delay-100 ease-in-out" collapse-icon="iconify mdi&#45;&#45;chevron-down text-2xl rotate-180"> -->
        <PAccordionPanel v-for="location in locations" :key="location.id" :value="location.id" class="group">
          <PAccordionHeader class="relative after:absolute after:bottom-2 after:left-0 after:right-0 group-[.p-accordionpanel-active]:after:block after:h-px after:hidden after:bg-gradient-to-r after:from-transparent after:from-20% after:via-surface-500/70 after:to-transparent after:to-80%">
            <div class="flex items-center text-base font-bold">
              <span
                  :class="locationLabel[location.id].classes"
                  v-tooltip="locationLabel[location.id].tooltip">
                {{ location.name }}
              </span>
              <div class="flex items-center py-1.5 ml-2 group" v-if="!isRestricted && location.freeShifts">
                <div class="mr-3 ml-1 w-2 h-2 bg-amber-500 rounded-full transition-colors group-hover:bg-amber-600 group-hover:dark:bg-amber-200"></div>
                <div class="hidden min-w-5 sm:block">
                  <div class="overflow-x-hidden w-0 text-sm text-gray-600 whitespace-nowrap transition-all group-hover:w-full dark:text-gray-400">
                    shifts still available
                  </div>
                </div>
              </div>
            </div>

            <template #toggleicon="{ active }">
              <span
                  class="iconify mdi--chevron-down text-2xl ml-auto transition-rotate duration-500 delay-100 ease-in-out"
                  :class="active ? 'rotate-180' : ''" />
            </template>
          </PAccordionHeader>
          <PAccordionContent>
            <div class="w-full">
              <div v-if="!isRestricted && location.freeShifts" class="flex mb-2 ml-3 sm:hidden group">
                <div class="flex items-center px-2 py-0.5 rounded-full border border-amber-500 dark:border-amber-600">
                  <div class="mr-1 w-2 h-2 bg-amber-500 rounded-full"></div>
                  <div class="text-sm text-amber-600 dark:text-amber-500">
                    free shifts still available at this location
                  </div>
                </div>
              </div>

              <div v-html="location.description" class="p-3 pt-0 w-full description dark:text-gray-100"></div>
              <div class="grid gap-x-2 gap-y-2 w-full sm:gap-y-4" :class="gridCols[location.max_volunteers]">
                <template v-for="shift in location.filterShifts" :key="shift.id">
                  <div class="self-center pt-4 pl-3 sm:pr-4 dark:text-gray-100">
                    {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
                  </div>
                  <div
                      v-for="(volunteer, index) in shift.filterVolunteers"
                      :key="index"
                      class="justify-self-center self-center pt-4">
                    <template v-if="volunteer">
                      <template v-if="volunteer.id === user.id">
                        <button
                            v-if="!isRestricted"
                            type="button"
                            class="block"
                            @click="toggleReservation(location.id, shift.id, false)">
                          <BookedSlot v-tooltip="`${volunteer.name}: Tap to un-reserve this shift`" />
                        </button>
                        <BookedSlot v-else />
                      </template>

                      <Male v-else-if="volunteer.gender === 'male'" v-tooltip="volunteer.name" />
                      <Female v-else-if="volunteer.gender === 'female'" v-tooltip="volunteer.name" />
                    </template>

                    <EmptySlot v-else-if="isRestricted" v-tooltip="'You cannot reserve a shift'" />
                    <EmptySlot
                        v-else-if="index === shift.filterVolunteers.length - 1 && shift.maxedFemales && user.gender === 'female'"
                        color="#79B9ED"
                        v-tooltip="'This slot can only be reserved by a brother'" />
                    <button
                        v-else
                        type="button"
                        class="block"
                        @click="toggleReservation(location.id, shift.id, true)">
                      <EmptySlot v-tooltip="'Tap to reserve this shift'" />
                    </button>
                  </div>
                  <div class="col-span-full px-3 rounded bg-surface-200 dark:bg-surface-800 dark:text-gray-50 sm:py-2">
                    <ul>
                      <li
                          v-for="(volunteer, index) in shift.filterVolunteers"
                          :key="index"
                          class="flex justify-between py-2 border-b border-gray-400 last:border-b-0">
                        <template v-if="volunteer">
                          <div>{{ volunteer.name }}</div>
                          <div>
                            Ph: <a :href="`tel:${volunteer.mobile_phone}`">
                              {{
                                volunteer.mobile_phone
                              }}
                            </a>
                          </div>
                        </template>

                        <template v-else>
                          <div>—</div>
                        </template>
                      </li>
                    </ul>
                  </div>
                </template>
              </div>
            </div>
          </PAccordionContent>
        </PAccordionPanel>
      </PAccordion>
    </div>
  </div>
</template>

<style>
.description {
  p {
    @apply mb-3;
  }

  ul, ol {
    @apply pl-5;

    li p {
      @apply mb-0.5;
    }
  }

  ul {
    @apply list-disc;
  }

  ol {
    @apply list-decimal;
  }

  strong {
    @apply font-bold
  }
}
</style>
