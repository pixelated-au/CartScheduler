import { computed } from "vue";
import type { useForm } from "@inertiajs/vue3";
import type { Number } from "ts-toolbelt";
import type { ComputedRef, Ref } from "vue";

// Define types from ShowRegularAvailabilityForm.vue
export type Day = keyof typeof days;
export type DayPredicate = `day_${Day}`;
export type NumDaysPredicate = `num_${Day}s`;

/**
 * Number.Range<n1, n2> returns a tuple. When appending [number], it acts as an index signature, retrieving the type of
 * all possible numeric indices. Since a tuple's elements are accessed by numeric indices, this effectively creates a
 * union of all the element types within the tuple.
 */
export type Hour = Number.Range<0, 23>[number];
export type DayOfMonthCount = Number.Range<0, 4>[number];

export type Availability =
  { [K in DayPredicate]: Hour[] } &
  { [K in NumDaysPredicate]: DayOfMonthCount } &
  {
    user_id?: number;
    comments?: string;
  };

interface Ranges {
  start: Hour;
  end: Hour;
}

export type FormattedTooltip = typeof tooltipFormat;

export const days = {
  monday: "Monday",
  tuesday: "Tuesday",
  wednesday: "Wednesday",
  thursday: "Thursday",
  friday: "Friday",
  saturday: "Saturday",
  sunday: "Sunday",
};

export const numberOfWeeks = {
  1: "1 week/month",
  2: "2 weeks/month",
  3: "3 weeks/month",
  4: "Every week",
};

function tooltipFormat(value: Hour): string {
    // seems to be a rounding issue with the slider. Math.round resolves it.
  value = Math.round(value) as Hour;
  if (value < 12) {
    return `${value}am`;
  }
  if (value === 12) {
    return `${value}pm`;
  }
  return `${value - 12}pm`;
}

export default function useAvailabilityActions(
  form: ReturnType<typeof useForm<Availability>>,
  ranges: Ref<Ranges>,
) {

  const buildRange = (start: Hour, end: Hour): Hour[] => {
    const range: Hour[] = [];
    for (let i = start; i <= end; i++) {
      range.push(i as Hour);
    }
    return range;
  };

  function toggleRosterDay(day: Day): ComputedRef<boolean> {
    return computed({
      get: () => (form[`num_${day}s`] as number) > 0,
      set: (value: boolean) => form[`num_${day}s`] = (value ? 1 : 0),
    });
  }

  function computedRange(day: Day): ComputedRef<Hour[]> {
    return computed({
      get: () => {
        const dayArray = form[`day_${day}`] as Hour[];
        return [dayArray[0], dayArray[dayArray.length - 1]];
      },
      set: (value: Hour[]) => {
        form[`day_${day}`] = (form[`num_${day}s`]) > 0
          ? buildRange(value[0], value[1])
          : buildRange(ranges.value.start, ranges.value.end);
      },
    });
  }

  return {
    computedRange,
    toggleRosterDay,
    tooltipFormat,
  };
}
