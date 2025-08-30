// noinspection JSUnusedGlobalSymbols

import type { VNode } from "vue";

declare module "primevue" {
  export interface AccordionHeaderSlots {
    // Overriding toggleicon slot as it's not defined in the PrimeVue type definition
    toggleicon(scope: {
      active: boolean;
    }): VNode[];
  }
}
