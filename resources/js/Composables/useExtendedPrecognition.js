import { useForm } from "laravel-precognition-vue-inertia";
import { inject } from "vue";

/**
 * @typedef {import("laravel-precognition-vue").Form} Form
 */

/**
 * @typedef {Object.<string, unknown>} Inputs
 */

/**
 * @typedef {import("laravel-precognition-vue-inertia").Form<Inputs>} ReturnValue
 */

/**
 * @typedef {object} Settings
 * @property {import("ziggy-js").ValidRouteName} routeName - The Laravel name of the route
 * @property {string|number|undefined|null} id - The id of the resource
 * @property {"post"|"put"|"patch"|"delete"} method - The HTTP method to use
 */

/**
 * @typedef {(settings: Settings, inputs: Inputs) => ReturnValue} ReturnFunction
 */

/**
 * @returns {function(Settings, Inputs): ReturnValue}
 */
export default function() {
  const route = inject("route");

  /**
   * Creates a precognition-enabled form instance for Inertia.js using Laravel Precognition.
   *
   * Uses HTTP PUT if an id is provided, otherwise uses POST. Automatically sets the route
   * and passes the provided inputs to the form instance.
   *
   * @type {ReturnFunction}
   */
  return (settings, inputs) => {
    let form;
    const method = settings.method || (settings.id ? "put" : "post");
    if (settings.id) {
      form = useForm(method, route(settings.routeName, settings.id), inputs);
    } else {
      form = useForm(method, route(settings.routeName), inputs);
    }

    return form;
  };
}
