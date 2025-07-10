/* eslint-disable @typescript-eslint/no-unused-vars,@typescript-eslint/no-empty-object-type */
// noinspection JSUnusedGlobalSymbols

import "vite/client";
import type { Page } from "@inertiajs/core";
import type {
  ComponentCustomOptions as _ComponentCustomOptions,
  ComponentCustomProperties as _ComponentCustomProperties,
  DefineComponent,
} from "vue";
import type { route as routeFn } from "ziggy-js";

declare global {
  const route: typeof routeFn;
}

declare module "vue" {
  const component: DefineComponent;

  interface ComponentCustomProperties {
    route: typeof route;
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }

  export interface InjectionKey<T> {
    __brand: T;
  }

  export function inject<T>(key: string | InjectionKey<T>): T | undefined;
  export function inject<T>(key: string | InjectionKey<T>, defaultValue: T): T;

  export function inject(key: "route"): RouteFunction;
  export function inject(key: "route", defaultValue: RouteFunction): RouteFunction;

  export default component;
}

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties extends _ComponentCustomProperties {}

  interface ComponentCustomOptions extends _ComponentCustomOptions {}
}

declare module "@inertiajs/core" {
  interface PageProps extends InertiaPageProps, AppPageProps {
  }
}
