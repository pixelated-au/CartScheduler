import { format, parse } from "date-fns";
import { computed } from "vue";
import type { WritableComputedRef } from "vue";

export default function(shift: string | undefined): WritableComputedRef<Date | undefined> {
  return computed<Date | undefined>({
    get: () => {
      if (!shift) {
        return new Date();
      }
      return parse(shift, "yyyy-MM-dd", new Date());
    },
    set: (value: Date | undefined) => shift = (value ? format(value, "yyyy-MM-dd") : undefined),
  });
}
