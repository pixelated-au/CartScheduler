import type { InjectionKey, Ref } from "vue";

export const EnableUserAvailability: InjectionKey<Ref<boolean>> = Symbol();
export const ReportTags: InjectionKey<Ref<App.Data.ReportTagData[]>> = Symbol();
