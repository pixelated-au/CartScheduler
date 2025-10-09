/* eslint-disable @typescript-eslint/no-unused-vars,@typescript-eslint/no-empty-object-type */
// noinspection JSUnusedGlobalSymbols

import "vite/client";
import { Page, PageProps as InertiaPageProps } from "@inertiajs/core";
import type { Form as PrecognitiveForm } from "laravel-precognition-vue-inertia";
import type { Axios } from "axios";
import type {
  ComponentCustomOptions as VueComponentCustomOptions,
  ComponentCustomProperties as VueComponentCustomProperties,
  DefineComponent,
} from "vue";
import type { route as routeFn } from "ziggy-js";
import type { AppPageProps } from "./laravel-request-helpers";

declare global {
  const route: typeof routeFn;
  const axios: Axios;
}

declare module "vue" {
  const component: DefineComponent;

  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $headManager: ReturnType<typeof createHeadManager>;
  }

  export function inject(key: "route"): RouteFn;

  export default component;
}

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    // For some reason, Ziggy needs to be in @vue/runtime-core
    route: typeof routeFn;
    $page: Page<AppPageProps>;
  }

  interface ComponentCustomProperties extends VueComponentCustomProperties {
  }

  interface ComponentCustomOptions extends VueComponentCustomOptions {
  }
}

declare module "@inertiajs/core" {
  interface PageProps extends InertiaPageProps, AppPageProps {
  }
}

declare module "@inertiajs/vue3" {
  export declare function usePage<T extends AppPageProps>(): Page<T>;
}

export type FormDataKeys<T extends Record<string, unknown>> = T extends T
  ? keyof T extends infer Key extends Extract<keyof T, string>
    ? T[Key] extends T
      ? `${Key}.${FormDataKeys<T[Key]>}` | Key
      : T[Key] extends Array<infer X extends Record<string, unknown>>
        ? `${Key}.${number}.${Extract<keyof X, string>}`
        : Key
    : never
  : never;

export type FormErrors<T> = Record<FormDataKeys<T>, string>;

export interface Form<Data extends Record<string, unknown>> extends PrecognitiveForm<Data> {
  errors: FormErrors<Data>;
}
