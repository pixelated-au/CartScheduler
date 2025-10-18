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
    const shiftDate = utcToZonedTime(date.date, shiftAvailability.value.timezone);

    map.set(
      [relativeDateToNow(shiftDate, now), format(shiftDate, "eo"), format(shiftDate, "MMM")],
      parseShiftsOnDate(date.shiftGroup, shiftDate),
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

const firstDate = computed(() => shifts.value.keys().next().value);
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
           class="pt-12 p-0 overflow-hidden md:overflow-y-hidden bg-white dark:bg-sub-panel-dark border std-border rounded justify-start"
           :class="[[ fullHeightList ? desktopListHeight : 'md:h-96' ],
                    {
                      'relative md:scroll-gradient' : isNotMobile && !fullHeightList,
                    }]">
        <dl class="flex flex-col gap-3 relative pl-12
                    before:absolute before:left-11 before:top-0 before:bottom-0 before:border-l before:border-l-neutral-400 before:border-dashed">
          <template v-for="[date, shiftsForDate] of shifts"
                    :key="date">
            <dt class="flex items-center h-12 font-semibold text-lg sm:text-md relative pl-8 size [&:not(:first-child)]:mt-6"
                :class="[{ 'before:bg-neutral-200 dark:before:bg-neutral-800': date === firstDate }]">
              {{ date[0] }}
              <div class="absolute -ml-1 -left-6 top-0 size-12 flex flex-col items-center justify-center before:rounded-full before:absolute before:inset-0 before:border before:border-neutral-400 before:bg-white before:-z-10 z-0">
                <div class="text-center leading-none text-s">{{ date[1] }}</div>
                <div class="text-center leading-none  text-xs text-gray-500">{{ date[2] }}</div>
              </div>
            </dt>
            <dd v-for="(shift, idx) in shiftsForDate"
                :key="idx"
                class="p-3">
              <div role="button"
                   class="group cursor-pointer rounded-s p-2 pb-1 flex flex-col items-start
                    md:hover:bg-neutral-100 dark:md:hover:bg-neutral-800 md:transition-[background-color,padding] md:duration-300 md:hover:font-bold md:hover:pl-3"
                   @click="selectShift(shift)">
                <span class="text-md group-hover:font-medium transition-[font-weight] duration-300">{{ shift.time }}</span>
                <span class="text-xl sm:text-lg uppercase text-gray-500 font-light group-hover:font-medium transition-[font-weight] duration-300 underline underline-offset-4 decoration-neutral-500 decoration-dotted sm:no-underline">
                  {{ shift.location }}
                </span>
              </div>
            </dd>
          </template>
        </dl>
      </div>
    </Transition>
  </div>
</template>
