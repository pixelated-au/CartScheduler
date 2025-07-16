import { useForm } from "laravel-precognition-vue-inertia";
import { inject } from "vue";
import type { Form } from "laravel-precognition-vue-inertia";
import type { FormDataConvertible } from "laravel-precognition-vue-inertia/dist/types";
import type { ValidRouteName } from "ziggy-js";

// Inputs is a generic object with string keys and unknown values
export type Inputs = Record<string, FormDataConvertible>;

export interface Settings {
  routeName: ValidRouteName;
  id?: string | number | null;
  method?: "post" | "put" | "patch" | "delete";
}

export type ReturnValue = Form<Inputs>;

export type ReturnFunction = (settings: Settings, inputs: Inputs) => ReturnValue;

/**
 * Creates a precognition-enabled form instance for Inertia.js using Laravel Precognition.
 * Uses HTTP PUT if an id is provided, otherwise uses POST. Automatically sets the route
 * and passes the provided inputs to the form instance.
 */
export default function useExtendedPrecognition(): ReturnFunction {
  const route = inject("route");

  return (settings: Settings, inputs: Inputs): ReturnValue => {
    let form: ReturnValue;
    const method = settings.method || (settings.id ? "put" : "post");
    if (settings.id) {
      form = useForm(method, route(settings.routeName, settings.id), inputs);
    } else {
      form = useForm(method, route(settings.routeName), inputs);
    }
    return form;
  };
}
