import { route as routeFn } from "ziggy-js";

declare global {
  const route: typeof routeFn;
}
