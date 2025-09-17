/* eslint-disable @typescript-eslint/no-unused-vars,@typescript-eslint/no-empty-object-type */
// noinspection JSUnusedGlobalSymbols

import "vite/client";
import type { Page, PageProps as InertiaPageProps } from "@inertiajs/core";
import type { Axios } from "axios";
import type {
  ComponentCustomOptions as VueComponentCustomOptions,
  ComponentCustomProperties as VueComponentCustomProperties,
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
  uuid: string;
  name: string;
  email: string;
  gender: string;
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

interface Flash {
  title?: string;
  message?: string | undefined;
  position?: "top" | "bottom" | "center";
  /* @deprecated - Use message instead */
  banner?: string;
  /* @deprecated - Only use for 'success' messages */
  bannerStyle?: ToastSeverity extends string ? ToastSeverity : undefined;
}

interface Jetstream {
  jetstream: {
    flash: Flash;
    canCreateTeams: boolean;
    canUpdateProfileInformation: boolean;
    canUpdatePassword: boolean;
    canManageTwoFactorAuthentication: boolean;
    hasAccountDeletionFeatures: boolean;
    hasApiFeatures: boolean;
    hasTeamFeatures: boolean;
    managesProfilePhotos: boolean;
    hasEmailVerification: boolean;
  };
}

export type AppPageProps<
  T extends Record<string, unknown> | unknown[] = Record<string, unknown> | unknown[],
> = InertiaProps & Jetstream & FlashMessage & T;

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
  interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module "@inertiajs/vue3" {
  export declare function usePage<T extends AppPageProps>(): Page<T>;
}
