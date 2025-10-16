<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { format, set } from "date-fns";
import { utcToZonedTime } from "date-fns-tz";
import { computed, ref, useTemplateRef } from "vue";
import relativeDateToNow from "@/Utils/relativeDateToNow";

export type ShiftItem = {
  date: Date;
  time: string;
  location: string;
  locationId: number;
};

const { markerDates } = defineProps<{
  markerDates?: App.Data.AvailableShiftsData["shifts"];
}>();

const emit = defineEmits<{
  clicked: [shift: ShiftItem];
}>();

const selectedShift = defineModel<ShiftItem | undefined>({ required: true });

const page = usePage();

const shiftAvailability = computed(() => page.props.shiftAvailability);

const parseShiftsOnDate = (shiftGroup: App.Data.AvailableShiftsData["shifts"][string], currentDate: Date) => {
  return Object.values(shiftGroup)
    .flat()
    .map(
      (shift) => {
        const startTime = shift.start_time; // as "HH:MM:SS"
        const split = startTime.split(":");
        const modifiedDate = set(currentDate, {
          hours: parseInt(split[0]),
          minutes: parseInt(split[1]),
          seconds: parseInt(split[2]),
          milliseconds: 0,
        });
        return {
          date: modifiedDate,
          time: format(modifiedDate, "HH:mm a"),
          location: shift.location_name,
          locationId: shift.location_id,
        };
      },
    )
    .sort((a, b) => a.date.getTime() - b.date.getTime());
};

const shifts = computed<Map<string, Array<ShiftItem>>>(() => {
  const map = new Map();

  if (!markerDates) {
    return map;
  }

  const now = new Date();
  const x = Object.keys(markerDates)
    .map((date) => ({
      date: utcToZonedTime(date, shiftAvailability.value.timezone),
      shiftGroup: markerDates[date],
    }))
    .sort((a, b) => a.date.getTime() - b.date.getTime());

  for (const date of x) {
    const currentDate = utcToZonedTime(date.date, shiftAvailability.value.timezone);

    map.set(
      relativeDateToNow(currentDate, now),
      parseShiftsOnDate(date.shiftGroup, currentDate),
    );
  }
  return map;
});

const selectShift = async (shift: ShiftItem) => {
  selectedShift.value = shift;
  emit("clicked", shift);
};

const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller("sm");
const isNotMobile = breakpoints.greaterOrEqual("sm");

const expandShiftList = ref(true);
const fullHeightList = computed({
  get: () => !expandShiftList.value,
  set: (value) => {
    expandShiftList.value = !value;
  },
});

const showList = computed(() => {
  if (isNotMobile.value) {
    return true;
  }
  return expandShiftList.value;
});

const list = useTemplateRef("list");

const toggleShiftList = () => {
  expandShiftList.value = !expandShiftList.value;
  if (isMobile.value) return;

  void toggleLargeStyle(list.value as HTMLElement);
};

const desktopListHeight = computed(() => {
  if (isMobile.value) {
    return "";
  }
  return `${list.value?.scrollHeight}px`;
});

const transition = "height 500ms cubic-bezier(0.5, 1, 0.89, 1), margin 500ms cubic-bezier(0.5, 1, 0.89, 1)";
const hideMobileList = (el: Element) => {
  if (isNotMobile.value) return;

  const element = el as HTMLElement;
  element.style.height = `${element.scrollHeight}px`;
  // element.style.marginTop = "1rem";
  element.style.transition = transition;
  // element.style.marginTop = "0px";
  element.style.height = "0px";
};

const showMobileList = (el: Element) => {
  if (isNotMobile.value) return;

  const element = el as HTMLElement;
  element.style.height = "0px";
  // element.style.marginTop = "0px";
  element.style.transition = transition;
  // element.style.marginTop = "1rem";
  element.style.height = `${element.scrollHeight}px`;
};

const toggleLargeStyle = async (element: HTMLElement) => {
  element.style.transition = "height 500ms ease-out";
};

function resetStyle(el: Element) {
  console.log("isMobile", isMobile.value);
  const element = el as HTMLElement;
  element.style.height = "";
  // element.style.marginTop = "";
  element.style.transition = "";
}
</script>

<template>
  <div class="relative">
    <button type="button"
            class="absolute top-2 border rounded [transition:transform_.5s,border-color_1s] px-2 text-sm flex items-center gap-1 z-10"
            :class="[{
              'right-2 border-transparent underline underline-offset-4 decoration-neutral-950/50 decoration-dotted dark:decoration-neutral-50/50': expandShiftList && isMobile,
              '-translate-y-11 std-border py-1 right-0': !expandShiftList && isMobile,
              'right-2 border-none': isNotMobile,
            }]"
            @click="toggleShiftList">
      <span v-if="isMobile" class="font-light">{{ expandShiftList ? "hide" : "show" }} list</span>
      <span v-else class="font-light">{{ fullHeightList ? "shrink" : "expand" }} list</span>

      <span v-if="isMobile"
            class="iconify mdi--arrow-collapse-up transition-transform duration-500 delay-100 text-gray-500"
            :class="[{
              'rotate-180': !expandShiftList,
            }]" />

      <template v-else>
        <span class="iconify mdi--arrow-collapse-down transition-transform duration-500 delay-100 text-gray-500 "
              :class="[{
                'rotate-180': fullHeightList,
              }]" />
      </template>
    </button>
    <Transition @enter="showMobileList($event)"
                @after-enter="resetStyle"
                @leave="hideMobileList($event)"
                @after-leave="resetStyle">
      <div ref="list"
           v-show="showList"
           class="mt-0 md:overflow-y-auto bg-white dark:bg-sub-panel-dark border std-border rounded justify-start overflow-hidden"
           :class="[[ fullHeightList ? desktopListHeight : 'md:h-96' ],
                    {
                      'md:scroll-gradient' : isNotMobile && !fullHeightList,
                    }]">
        <ul class="flex flex-col gap-3 p-3">
          <li v-for="[date, shiftsForDate] of shifts" :key="date">
            <span class="text-sm font-bold">{{ date }}</span>
            <ul class="grid grid-cols-2 gap-1 font-medium">
              <li v-for="(shift, idx) in shiftsForDate"
                  :key="idx"
                  class="shadow md:shadow-sm bg-panel dark:bg-panel-dark">
                <div role="button"
                     class="cursor-pointer border std-border rounded p-2 flex flex-col items-start"
                     @click="selectShift(shift)">
                  <span class="text-sm">{{ shift.time }}</span>
                  <span class="uppercase text-gray-500 font-light">{{ shift.location }}</span>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>
