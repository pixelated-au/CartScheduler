import { format, parse } from "date-fns";
import { computed } from "vue";
import type { WritableComputedRef, Ref } from "vue";

type DateKey = keyof Pick<App.Data.ShiftAdminData, "available_from" | "available_to">;

export default function(shift: Ref<App.Data.ShiftAdminData>, dateKey: DateKey): WritableComputedRef<Date | null> {
  return computed<Date | null>({
    get: () => {
      console.log("Getting date", shift.value[dateKey]);
      if (!shift.value[dateKey]) {
        return null;
      }
      return parse(shift.value[dateKey], "yyyy-MM-dd", new Date());
    },
    set: (value: Date | null) => {
      console.log("Setting date", value);
      shift.value[dateKey] = (value ? format(value, "yyyy-MM-dd") : null);
    },
    // set: (value: Date | null) => shift = (value ? format(value, "yyyy-MM-dd") : null),
  });
}
