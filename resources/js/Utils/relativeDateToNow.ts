import { differenceInDays, format } from "date-fns";

export default function(date: Date, baseDate: Date) {
  const diff = Math.abs(differenceInDays(date, baseDate));
  if (diff === 0) {
    return "Today";
  }
  if (diff === 1) {
    return "Tomorrow";
  }
  if (diff < 6) {
    return format(date, "EEEE");
  }
  if (diff < 13) {
    return format(date, "'Next' EEEE 'the' eo");
  }
  return format(date, "EEEE, do MMMM");
}
