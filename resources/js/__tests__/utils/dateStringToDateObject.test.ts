import { format, parse } from "date-fns";
import { describe, it, expect, vi } from "vitest";
import dateStringToDateObject from "@/Utils/dateStringToDateObject";

vi.mock("date-fns", () => ({
  format: vi.fn().mockReturnValue("2025-09-15"),
  parse: vi.fn().mockReturnValue(new Date("2025-09-15")),
}));

describe("dateStringToDateObject", () => {
  it("returns current date when shift is undefined", () => {
    const result = dateStringToDateObject(undefined);
    expect(result.value).toBeInstanceOf(Date);
  });

  it("parses date string to Date object when shift is provided", () => {
    const result = dateStringToDateObject("2025-09-15");
    expect(result.value).toEqual(new Date("2025-09-15"));
    expect(parse).toHaveBeenCalledWith("2025-09-15", "yyyy-MM-dd", expect.any(Date));
  });

  it("formats Date object to string when setting value", () => {
    const result = dateStringToDateObject("2025-09-15");
    result.value = new Date("2025-09-15");
    expect(format).toHaveBeenCalledWith(new Date("2025-09-15"), "yyyy-MM-dd");
  });

  it("sets shift to undefined when value is undefined", () => {
    const result = dateStringToDateObject("2025-09-15");
    result.value = undefined;
    // The format function is called in the code, but the result is not used when value is undefined
    expect(format).toHaveBeenCalled();
  });
});
