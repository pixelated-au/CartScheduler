import { createGlobalState, useStorage } from "@vueuse/core";

type StoredValues = {
  dismissedAvailabilityOn: Date | undefined;
};

export const useGlobalState = createGlobalState(
  () =>
    useStorage("cart-scheduler-store", {
      dismissedAvailabilityOn: undefined,
    } as StoredValues),
);
