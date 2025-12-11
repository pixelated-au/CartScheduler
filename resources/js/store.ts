import { ref, watch } from "vue";

export enum Labels {
  Gender = "Gender",
  Appointment = "Appointment",
  ServingAs = "Serving As",
  MaritalStatus = "Marital Status",
  BirthYear = "Birth Year",
  ResponsibleBrother = "Is Responsible Bro?",
  MobilePhone = "Phone",
  AvailabilityComments = "Comments",
  LastLocation = "Last Location",
}

export type LocalStore = {
  dismissedAvailabilityOn?: Date;
  columnFilters: {
    gender: { label: Labels.Gender; value: boolean };
    appointment: { label: Labels.Appointment; value: boolean };
    servingAs: { label: Labels.ServingAs; value: boolean };
    maritalStatus: { label: Labels.MaritalStatus; value: boolean };
    birthYear: { label: Labels.BirthYear; value: boolean };
    responsibleBrother: { label: Labels.ResponsibleBrother; value: boolean };
    mobilePhone: { label: Labels.MobilePhone; value: boolean };
    availabilityComments: { label: Labels.AvailabilityComments; value: boolean };
    lastLocation: { label: Labels.LastLocation; value: boolean };
  };
  shiftView: "list" | "calendar";
};

const defaults: LocalStore = {
  dismissedAvailabilityOn: undefined,
  columnFilters: {
    gender: { label: Labels.Gender, value: false },
    appointment: { label: Labels.Appointment, value: false },
    servingAs: { label: Labels.ServingAs, value: false },
    maritalStatus: { label: Labels.MaritalStatus, value: false },
    birthYear: { label: Labels.BirthYear, value: false },
    responsibleBrother: { label: Labels.ResponsibleBrother, value: false },
    mobilePhone: { label: Labels.MobilePhone, value: false },
    availabilityComments: { label: Labels.AvailabilityComments, value: false },
    lastLocation: { label: Labels.LastLocation, value: false },
  },
  shiftView: "calendar",
};

const STORAGE_KEY = "cart-scheduler-store";

// Check if stored data has the same structure as defaults
function hasValidStructure(stored: any): stored is LocalStore {
  if (!stored || typeof stored !== "object") return false;

  // Check top-level keys
  if (!stored.columnFilters || !stored.shiftView) return false;

  // Check all columnFilters keys exist
  const expectedKeys = Object.keys(defaults.columnFilters);
  const storedKeys = Object.keys(stored.columnFilters || {});

  // If keys don't match, structure is invalid
  const keysMatch = expectedKeys.every((key) => storedKeys.includes(key)) && 
                    storedKeys.every((key) => expectedKeys.includes(key));

  if (!keysMatch) return false;

  // Check if all labels match
  for (const key of expectedKeys) {
    const expectedLabel = defaults.columnFilters[key as keyof typeof defaults.columnFilters].label;
    const storedLabel = stored.columnFilters[key]?.label;

    if (expectedLabel !== storedLabel) {
      return false; // Label changed, need to reset
    }
  }

  return true;

}

// Simple load with structure validation
const loadState = (): LocalStore => {
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored) {
    try {
      const parsed = JSON.parse(stored);
      // If structure is invalid, reset to defaults
      if (!hasValidStructure(parsed)) {
        return defaults;
      }
      return parsed;
    } catch {
      return defaults;
    }
  }
  return defaults;
};

// Create global state
const globalState = ref<LocalStore>(loadState());

// Auto-save to localStorage whenever state changes
watch(
  globalState,
  (newState) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(newState));
  },
  { deep: true },
);

// Export as a composable function
export const useGlobalState = () => globalState;