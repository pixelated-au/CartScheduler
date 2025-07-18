import { computed, ComputedRef, Ref } from "vue";

// Define types from ShowRegularAvailabilityForm.vue
type Day = "monday" | "tuesday" | "wednesday" | "thursday" | "friday" | "saturday" | "sunday";
type DayPredicate = `day_${Day}`;
type NumDaysPredicate = `num_${Day}s`;
type Hour = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12 | 13 | 14 | 15 | 16 | 17 | 18 | 19 | 20 | 21 | 22 | 23;
type DayOfMonthCount = 0 | 1 | 2 | 3 | 4;

type Availability = { [K in DayPredicate]?: Hour[]; } &
  { [K in NumDaysPredicate]?: DayOfMonthCount; } &
  {
    user_id?: number;
    comments?: string;
  };

interface Ranges {
  start: number;
  end: number;
}

interface WeekOption {
  value: number;
  label: string;
}

interface UseAvailabilityActionsReturn {
  toggleRosterDay: (day: Day) => ComputedRef<boolean>;
  computedRange: (day: Day) => ComputedRef<number[]>;
  tooltipFormat: (value: number) => string;
  numberOfWeeks: WeekOption[];
}

export default function useAvailabilityActions(
  form: Availability,
  ranges: Ref<Ranges>
): UseAvailabilityActionsReturn {

  const buildRange = (start: number, end: number): Hour[] => {
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

  function computedRange(day: Day): ComputedRef<number[]> {
    return computed({
      get: () => {
        const dayArray = form[`day_${day}`] as number[];
        return [dayArray[0], dayArray[dayArray.length - 1]];
      },
      set: (value: number[]) => {
        form[`day_${day}`] = (form[`num_${day}s`] as number) > 0
          ? buildRange(value[0], value[1])
          : buildRange(ranges.value.start, ranges.value.end);
      },
    });
  }

  function tooltipFormat(value: number): string {
    value = Math.round(value); // seems to be a rounding issue with the slider. This fixes it.
    if (value < 12) {
      return `${value}am`;
    }
    if (value === 12) {
      return `${value}pm`;
    }
    return `${value - 12}pm`;
  }

  const numberOfWeeks: WeekOption[] = [
    { value: 1, label: "1 week/month" },
    { value: 2, label: "2 weeks/month" },
    { value: 3, label: "3 weeks/month" },
    { value: 4, label: "Every week" },
  ];

  return {
    toggleRosterDay,
    computedRange,
    tooltipFormat,
    numberOfWeeks,
  };
}
