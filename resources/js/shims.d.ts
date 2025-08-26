/* eslint-disable @typescript-eslint/no-unused-vars,@typescript-eslint/no-empty-object-type */
// noinspection JSUnusedGlobalSymbols

import "vite/client";
import type { Page, PageProps } from "@inertiajs/core";
import type { Axios } from "axios";
import type {
  ComponentCustomOptions as _ComponentCustomOptions,
  ComponentCustomProperties as _ComponentCustomProperties,
  DefineComponent,
} from "vue";
import type { ToastSeverity } from "@/Composables/useToast";
import type { route as routeFn } from "ziggy-js";

declare global {
  const route: typeof routeFn;
  const axios: Axios;
}

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  gender: string;
};

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    // For some reason, Ziggy needs to be in @vue/runtime-core
    route: typeof routeFn;
  }
}

declare module "vue" {
  const component: DefineComponent;

  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }

  export function inject(key: "route"): RouteFn;

  export default component;
}

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties extends _ComponentCustomProperties {
  }

  interface ComponentCustomOptions extends _ComponentCustomOptions {
  }
}

declare module "@inertiajs/vue3" {
  interface Flash {
    title?: string;
    message?: string | undefined;
    position?: "top" | "bottom" | "center";
    /* @deprecated - Use message instead */
    banner?: string;
    /* @deprecated - Only use for 'success' messages */
    bannerStyle?: ToastSeverity extends string ? ToastSeverity : undefined;
  }

  type FlashMessage = {
    jetstream: {
      flash: Flash;
    };
  };

  export type InertiaProps = {
    pagePermissions: {
      canAdmin?: true;
      canEditSettings?: true;
    };
    shiftAvailability: {
      timezone: string;
      duration: number;
      period: App.Enums.DBPeriod;
      releasedDaily: boolean;
      weekDayRelease: "SUN" | "MON" | "TUE" | "WED" | "THU" | "FRI" | "SAT";
      systemShiftStartHour: number;
      systemShiftEndHour: number;
    };
    hasUpdate?: boolean;
    enableUserAvailability?: boolean;
    needsToUpdateAvailability?: boolean;
    enableUserLocationChoices?: boolean;
    isUnrestricted?: true;
    user: AuthUser;
  };

  export declare function usePage<SharedProps extends PageProps & InertiaProps & FlashMessage>(): Page<SharedProps>;
}
