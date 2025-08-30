import { createGlobalState, useStorage } from "@vueuse/core";

export type LocalStore = typeof defaults;

const defaults = {
  dismissedAvailabilityOn: undefined,
  // used to filter admin volunteer rostering table
  columnFilters: {
    gender: { label: "Gender", value: false },
    appointment: { label: "Appointment", value: false },
    servingAs: { label: "Serving As", value: false },
    maritalStatus: { label: "Marital Status", value: false },
    birthYear: { label: "Birth Year", value: false },
    responsibleBrother: { label: "Is Responsible Bro?", value: false },
    mobilePhone: { label: "Phone", value: false },
  },
};

export const useGlobalState = createGlobalState(
  () => useStorage<LocalStore>(
    "cart-scheduler-store",
    defaults,
    localStorage,
    { mergeDefaults: true },
  ),
);
