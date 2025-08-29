import type { InjectionKey, Ref } from "vue";

export const EnableUserAvailability: InjectionKey<boolean> = Symbol();
export const ReportTags: InjectionKey<Ref<App.Data.ReportTagData[]>> = Symbol();
