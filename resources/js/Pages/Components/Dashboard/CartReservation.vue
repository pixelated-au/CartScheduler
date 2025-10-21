<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { isAxiosError } from "axios";
import { format, isSameDay, parse } from "date-fns";
import { computed, onMounted, ref, watch } from "vue";
import Accordion from "@/Components/Accordion.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import ComponentSpinner from "@/Components/ComponentSpinner.vue";
import EmptySlot from "@/Components/Icons/EmptySlot.vue";
import User from "@/Components/Icons/User.vue";
import useLocationFilter from "@/Composables/useLocationFilter";
import useToast from "@/Composables/useToast";
import useShiftMarkers from "@/Pages/Components/Dashboard/composables/useShiftMarkers";
import DatePicker from "@/Pages/Components/Dashboard/DatePicker.vue";
import ShiftList from "@/Pages/Components/Dashboard/ShiftList.vue";
import { useGlobalState } from "@/store";
import relativeDateToNow from "@/Utils/relativeDateToNow";
import type { LocationsOnDate } from "@/Pages/Components/Dashboard/DatePicker.vue";
import type { ShiftItem as SelectedShift } from "@/Pages/Components/Dashboard/ShiftList.vue";

const page = usePage();
const toast = useToast();

const user = computed(() => page.props.auth.user);
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

const shiftMarkers = useShiftMarkers(serverDates);

const state = useGlobalState();
const shiftView = computed({
  get() {
    return state.value.shiftView;
  },
  set(value) {
    state.value.shiftView = value;
  },
});

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
  }
};

const locationsOnDates = ref<LocationsOnDate[]>([]);
const locationsForSelectedDate = computed(() =>
  shiftMarkers.value.map(
    (marker) => ({
      locations: marker.locations,
      date: marker.date,
    }),
  ).filter(
    (location) => isSameDay(location.date, date.value),
  ));

const setLocationMarkers = (locations: LocationsOnDate[]) => {
  locationsOnDates.value = locations;
};
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
        firstReservationForUser.value = selectedShift.value?.locationId || location.id;
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

const selectedShift = ref<SelectedShift | undefined>(undefined);

watch(selectedShift, (val) => {

  if (!val) {
    return;
  }
  date.value = val.date;

});

const locationRefs = ref<Record<App.Data.LocationData["id"], HTMLElement>>({});
const setLocationRef = (id: App.Data.LocationData["id"], el: HTMLElement) => {
  locationRefs.value[id] = el;
};

const breakpoints = useBreakpoints(breakpointsTailwind);
const isNotMobile = breakpoints.greaterOrEqual("sm");

const scrollToLocation = async (itemKey: App.Data.LocationData["id"]) => {
  if (isNotMobile.value) {
    return;
  }
  const element = locationRefs.value[itemKey];

  element.scrollIntoView({ behavior: "smooth" });
};

onMounted(() => {
  void getShifts();
});

const transitionContainerHeight = ref<string>("auto");

const beforeEnter = (el: Element) => {
  const element = el as HTMLElement;
  element.style.opacity = "0";
  element.style.transform = "translateX(110%)";
};

const enter = (el: Element, done: () => void) => {
  const element = el as HTMLElement;
  console.log("setInitialHeight", element.scrollHeight);
  transitionContainerHeight.value = `${element.scrollHeight}px`;
  element.style.opacity = "1";
  element.style.transform = "translateX(0)";
  done();
};

let cancelTimeout = 0;
const afterEnter = (_: Element) => {
  clearTimeout(cancelTimeout as number);
  console.log("resetHeight to 'auto'", transitionContainerHeight.value);
  cancelTimeout = window.setTimeout(() => {
    console.log("bam!");
    transitionContainerHeight.value = "auto";
  }, 500);
};

const beforeLeave = async (el: Element) => {
  const element = el as HTMLElement;
  console.log("setDepartingHeight", element);
  transitionContainerHeight.value = `${element.scrollHeight}px`;
  element.style.opacity = "0";
  element.style.transform = "translateX(-110%)";
  element.style.height = `${element.scrollHeight}px`;
};
</script>

<template>
  <div class="grid gap-3 grid-cols-1 sm:grid-cols-[20rem_3fr] sm:items-stretch">
    <div class="">
      <ComponentSpinner :show="!locations" class="flex flex-col">
        <div class="pb-2 grid grid-cols-2 gap-2">
          <PButton :disabled="shiftView === 'calendar'"
                   variant="outlined"
                   severity="info"
                   size="small"
                   @click="shiftView = 'calendar'">
            <span class="iconify mdi--calendar-month-outline"/>
            Calendar
          </PButton>
          <PButton :disabled="shiftView === 'list'"
                   variant="outlined"
                   severity="info"
                   size="small"
                   @click="shiftView = 'list'">
            <span class="iconify mdi--timeline-text-outline"/>
            Timeline
          </PButton>
        </div>
        <div ref="transitionContainer" class="transition-container">
          <Transition mode="out-in"
                      @before-enter="beforeEnter"
                      @enter="enter"
                      @after-enter="afterEnter"
                      @before-leave="beforeLeave">
            <ShiftList v-if="shiftView === 'list'"
                       class="w-full"
                       v-model="selectedShift"
                       :marker-dates="serverDates"
                       @clicked="scrollToLocation($event.locationId)" />
            <DatePicker v-else
                        v-model:date="date"
                        :shiftMarkers
                        :max-date="maxReservationDate"
                        :free-shifts="freeShifts"
                        :marker-dates="serverDates"
                        @locations-for-day="setLocationMarkers" />
          </Transition>
        </div>
      </ComponentSpinner>
    </div>
    <div>
      <ComponentSpinner :show="isLoading" class="min-h-[200px] sm:min-h-full">
        <Accordion v-model="accordionExpandIndex" class="border std-border rounded border-b-0">
          <AccordionPanel v-for="location in locations" :key="location.id" :value="location.id">
            <template #title>
              <div :ref="(el) => setLocationRef(location.id,el as HTMLElement)"
                   class="flex items-center text-base font-bold p-2 bg-">
                <span :class="locationLabel[location.id].classes"
                      v-tooltip="locationLabel[location.id].tooltip">
                  {{ location.name }}
                </span>
                <div class="flex items-center py-1.5 ml-2 group"
                     v-if="!isRestricted && location.freeShifts">
                  <div class="mr-3 ml-1 w-2 h-2 bg-amber-500 rounded-full transition-colors group-hover:bg-amber-600 group-hover:dark:bg-amber-200"></div>
                  <div class="hidden min-w-5 sm:block">
                    <div class="overflow-x-hidden w-0 text-sm text-gray-600 whitespace-nowrap transition-[width] group-hover:w-full dark:text-gray-400">
                      shifts still available
                    </div>
                  </div>
                </div>
              </div>
            </template>

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
                  <div class="self-center pt-4 pl-3 sm:pr-4 dark:text-gray-100 flex flex-col">
                    <span>{{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}</span>
                    <span class="text-xs">{{ relativeDateToNow(date, new Date()) }}</span>
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
          </AccordionPanel>
        </Accordion>
      </ComponentSpinner>
    </div>
  </div>
</template>

<!--suppress CssUnusedSymbol -->
<style scoped>
.transition-container {
    --timing: 250ms;
    --delay: 150ms;
    height: v-bind(transitionContainerHeight);
    transition: height var(--timing) ease-in-out;
}

.transition-container > div {
    transition: transform var(--timing) var(--delay) ease-out, opacity var(--timing) var(--delay) ease-out;
}

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
