import type { Editor } from "@tiptap/vue-3";
import type { InjectionKey, Ref, ShallowRef } from "vue";

export type AccordionContext<AllowedModelValues> = {
  registerPanel: () => number;
  isOpen: (index: AllowedModelValues) => boolean;
  toggle: (index: AllowedModelValues) => void;
  onHeaderKeydown: (e: KeyboardEvent, index: number) => void;
  setHeaderRef: (index: number, el: HTMLElement | null) => void;
};

export const EnableUserAvailability: InjectionKey<boolean> = Symbol();
export const ReportTags: InjectionKey<Ref<App.Data.ReportTagData[]>> = Symbol();
export const AccordionContext: InjectionKey<AccordionContext<unknown>> = Symbol();
export const HtmlEditor: InjectionKey<ShallowRef<Editor | undefined>> = Symbol();
