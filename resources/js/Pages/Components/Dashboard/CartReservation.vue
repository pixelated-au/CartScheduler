<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { isAxiosError } from "axios";
import { format, isSameDay, parse } from "date-fns";
import { computed, onMounted, ref, watch } from "vue";
import ComponentSpinner from "@/Components/ComponentSpinner.vue";
import EmptySlot from "@/Components/Icons/EmptySlot.vue";
import User from "@/Components/Icons/User.vue";
import useLocationFilter from "@/Composables/useLocationFilter";
import useToast from "@/Composables/useToast";
import DatePicker from "@/Pages/Components/Dashboard/DatePicker.vue";
import type { LocationsOnDate } from "@/Pages/Components/Dashboard/DatePicker.vue";

const page = usePage();
const user = computed(() => page.props.auth.user);

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
const toggleReservation = async (locationId: number, shiftId: number, toggleOn: boolean) => {
  if (isReserving.value) {
    return;
  }
  const timeoutId = setTimeout(() => isLoading.value = true, 1000);

  try {
    reservationWatch.pause();
    isReserving.value = true;

    const response = await axios.post<string>(route("reserve.shift"), {
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
    if (!isAxiosError(e) || !e.response?.data) {
      throw e;
    }
    toast.error(e.response.data.message, "Error!", { timeout: 4000 });
    if (e.response.data.error_code === 100) {
      await getShifts(false);
    }
  } finally {
    isReserving.value = false;
    clearTimeout(timeoutId);
    isLoading.value = false;
    reservationWatch.resume();
    // await (() =>     reservationWatch.resume(), 300);
  }
};

const locationsOnDates = ref<LocationsOnDate[]>([]);
const locationsForSelectedDate = computed(() => {
  return locationsOnDates.value.filter(
    (location) => isSameDay(location.date, date.value),
  );
});

const setLocationMarkers = (locations: LocationsOnDate[]) => locationsOnDates.value = locations;
const hasShift = (location: App.Data.LocationData) => locationsForSelectedDate.value?.findIndex(
  (date) => date?.locations.includes(location.id),
) >= 0;

const today = new Date();
const formatTime = (time: string) => format(parse(time, "HH:mm:ss", today), "h:mm a");

const isRestricted = computed(() => !usePage().props.isUnrestricted);

const firstReservationForUser = ref<number | undefined>();
const locationLabel = ref<Record<number, { classes: string[]; tooltip?: string }>>({});

const markRosteredLocations = () => {
  let hasSetFirstReservationForUser = false;
  const labelData: Record<number, { classes: string[]; tooltip?: string }> = {};
  firstReservationForUser.value = undefined;
  for (const location of locations.value) {
    const classes = [];
    let tooltip = undefined;
    if (hasShift(location)) {
      classes.push(...["text-green-800", "dark:text-green-300", "border-b-2", "border-green-500"]);
      tooltip = "You have at least one shift";
      if (!hasSetFirstReservationForUser) {
        firstReservationForUser.value = location.id;
        hasSetFirstReservationForUser = true;
      }
    } else {
      classes.push("dark:text-gray-200");
    }
    labelData[location.id] = { classes, tooltip };
  }
  locationLabel.value = labelData;
  if (!hasSetFirstReservationForUser) {
    firstReservationForUser.value = undefined;
  }
};

watch(locationsForSelectedDate, () => {
  markRosteredLocations();
});

const accordionExpandIndex = ref<number | undefined>(undefined);

const reservationWatch = watch(firstReservationForUser, (val) => {
  if (val === undefined) {
    accordionExpandIndex.value = undefined;
    return;
  }
  accordionExpandIndex.value = val;
});

onMounted(() => {
  void getShifts();
});
</script>

<template>
  <div class="grid gap-3 grid-cols-1 sm:grid-cols-[20rem_3fr] sm:items-stretch">
    <div class="pb-3">
      <ComponentSpinner :show="!locations">
        <DatePicker v-model:date="date"
                    :max-date="maxReservationDate"
                    :free-shifts="freeShifts"
                    :user="user"
                    :marker-dates="serverDates"
                    @locations-for-day="setLocationMarkers" />
      </ComponentSpinner>
      <div class="text-sm text-center text-gray-500">Blue squares indicate free shifts</div>
    </div>
    <div>
      <ComponentSpinner :show="isLoading" class="min-h-[200px] sm:min-h-full">
        <PAccordion v-model:value="accordionExpandIndex" class="border std-border rounded border-b-0">
          <PAccordionPanel v-for="location in locations" :key="location.id" :value="location.id" class="group">
            <PAccordionHeader class="relative after:absolute after:bottom-2 after:left-0 after:right-0 group-[.p-accordionpanel-active]:after:block after:h-px after:hidden after:bg-gradient-to-r after:from-transparent after:from-20% after:via-surface-500/70 after:to-transparent after:to-80%">
              <div class="flex items-center text-base font-bold">
                <span :class="locationLabel[location.id].classes"
                      v-tooltip="locationLabel[location.id].tooltip">
                  {{ location.name }}
                </span>
                <div class="flex items-center py-1.5 ml-2 group"
                     v-if="!isRestricted && location.freeShifts">
                  <div class="mr-3 ml-1 w-2 h-2 bg-amber-500 rounded-full transition-colors group-hover:bg-amber-600 group-hover:dark:bg-amber-200"></div>
                  <div class="hidden min-w-5 sm:block">
                    <div class="overflow-x-hidden w-0 text-sm text-gray-600 whitespace-nowrap transition-all group-hover:w-full dark:text-gray-400">
                      shifts still available
                    </div>
                  </div>
                </div>
              </div>

              <template #toggleicon="{ active }">
                <!-- Note, the active slotProp exists but isn't documented -->
                <span class="iconify mdi--chevron-down text-2xl ml-auto transition-rotate duration-500 delay-100 ease-in-out"
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

                <div v-html="location.description"
                     class="p-3 pt-0 w-full description dark:text-gray-100"></div>
                <div class="grid gap-x-2 gap-y-2 w-full sm:gap-y-4"
                     :class="gridCols[location.max_volunteers as keyof typeof gridCols]">
                  <template v-for="shift in location.filterShifts" :key="shift.id">
                    <div class="self-center pt-4 pl-3 sm:pr-4 dark:text-gray-100">
                      {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
                    </div>
                    <div v-for="(volunteer, index) in shift.volunteers"
                         :key="index"
                         class="justify-self-center self-center pt-4">
                      <template v-if="volunteer">
                        <template v-if="user.uuid && volunteer.uuid === user.uuid">
                          <button v-if="!isRestricted"
                                  type="button"
                                  class="block"
                                  @click="toggleReservation(location.id, shift.id, false)">
                            <User status="reserved" v-tooltip="`${volunteer.name}: Tap to un-reserve this shift`" />
                          </button>
                          <User status="reserved" v-else />
                        </template>

                        <User status="male" v-else-if="volunteer.gender === 'male'" v-tooltip="volunteer.name" />
                        <User status="female"
                              v-else-if="volunteer.gender === 'female'"
                              v-tooltip="volunteer.name" />
                      </template>

                      <EmptySlot v-else-if="isRestricted" v-tooltip="'You cannot reserve a shift'" />
                      <EmptySlot v-else-if="index === shift.volunteers.length - 1 && shift.maxedFemales && user.gender === 'female'"
                                 color="#79B9ED"
                                 v-tooltip="'This slot can only be reserved by a brother'" />
                      <button v-else
                              type="button"
                              class="block"
                              @click="toggleReservation(location.id, shift.id, true)">
                        <EmptySlot v-tooltip="'Tap to reserve this shift'" />
                      </button>
                    </div>
                    <div class="col-span-full px-3 rounded bg-surface-200 dark:bg-surface-800 dark:text-gray-50 sm:py-2">
                      <ul>
                        <li v-for="(volunteer, index) in shift.volunteers"
                            :key="index"
                            class="flex justify-between py-2 border-b border-gray-400 last:border-b-0">
                          <template v-if="volunteer">
                            <div>{{ volunteer.name }}</div>
                            <div>
                              Ph:
                              <a :href="`tel:${volunteer.mobile_phone}`">{{ volunteer.mobile_phone }}</a>
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
      </ComponentSpinner>
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
