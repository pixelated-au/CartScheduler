import type { Location, Shift } from "@/Composables/useLocationFilter";

type Digit = "0" | "1" | "2" | "3" | "4" | "5" | "6" | "7" | "8" | "9";
type NonZeroDigit = Exclude<Digit, "0">;
type Decade = Extract<Digit, "2" | "3" | "4">;

type Year = `20${Decade}${Digit}`; // 20[2-4][0-9]
type Month = `0${NonZeroDigit}` | `1${0 | 1 | 2}`;       // 01-12
type Day = `0${NonZeroDigit}` | `${"1" | "2"}${Digit}` | `3${0 | 1}`; // 01-31
type Hour = `${"0" | "1"}${Digit}` | `2${0 | 1 | 2 | 3}`; // 00-23
type SixtyCount = `${"0" | "1" | "2" | "3" | "4" | "5"}${Digit}`; // 00-59

export type IsoDate = `${Year}-${Month}-${Day}`; // 20[2-4][0-9]-[01-12]-[01-31]
export type TwentyFourHourTime = `${Hour}:${SixtyCount}:${SixtyCount}`; // [00-23]:[00-59]:[00-59]
export type IsoDateTime = `${IsoDate}T${TwentyFourHourTime}`; // 20[2-4][0-9]-[01-12]-[01-31]T[00-23]:[00-59]:[00-59]

export type ErrorBag = Record<string, string[]>

export type LaravelValidationResponse = {
  message: string;
  errors: ErrorBag;
}

export type CssClass = string | Record<string, boolean> | Array<CssClass>;

export type DateMark = {
  date: Date;
  type: "line";
  color: "#0E9F6E";
  locations: number[];
};

export type AssignVolunteerPayload = {
  volunteerId: number;
  volunteerName: string;
  location: Location;
  shift: Shift;
};
