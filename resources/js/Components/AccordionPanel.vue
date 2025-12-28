<script setup lang="ts" generic="AllowedModelValues">
import { computed, inject, onMounted, onUnmounted, useId, useTemplateRef } from "vue";
import { AccordionContext } from "@/Utils/provide-inject-keys";

const { value, disabled = false } = defineProps<{
  value: AllowedModelValues;
  disabled?: boolean;
}>();

const trigger = useTemplateRef<HTMLElement>("trigger");

const ctx = inject<(AccordionContext<AllowedModelValues>)>(AccordionContext);
if (!ctx) {
  throw new Error("AccordionPanel must be used within Accordion");
}

const index = ctx.registerPanel();
const open = computed(() => ctx.isOpen(value));

onMounted(() => ctx.setHeaderRef(index, trigger.value));
onUnmounted(() => ctx.setHeaderRef(index, null));

const uid = useId();

const headerId = computed(() => `${index}-header-${uid}`);
const panelId = computed(() => `${index}-panel-${uid}`);

function onClick() {
  if (disabled || !ctx) return;
  ctx.toggle(value);
}

function onKeydown(e: KeyboardEvent) {
  if (disabled || !ctx) return;
  ctx.onHeaderKeydown(e, index);
}

function toggleStyle(el: Element, isOpening: boolean) {
  const element = el as HTMLElement;
  element.style.height = isOpening ? "0px" : `${element.scrollHeight}px`;
  element.style.overflow = "hidden";
  element.style.transition = "height 250ms cubic-bezier(0.5, 1, 0.89, 1)";
  element.style.height = isOpening ? `${element.scrollHeight}px` : "0px";
}

function resetStyle(el: Element) {
  const element = el as HTMLElement;
  element.style.height = "";
  element.style.overflow = "";
  element.style.transition = "";
}
</script>

<template>
  <div class="border-b std-border-bottom bg-white dark:bg-sub-panel-dark">
    <div role="heading" aria-level="1">
      <button ref="trigger"
              class="flex rounded items-center justify-between w-full bg-transparent border-0 text-left px-2 py-1 cursor-pointer
              outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
              type="button"
              :id="headerId"
              :aria-expanded="open ? 'true' : 'false'"
              :aria-controls="panelId"
              :disabled="disabled"
              @click="onClick"
              @keydown="onKeydown">
        <slot name="title" />
        <span class="iconify mdi--chevron-down text-2xl ml-auto transition-rotate duration-500 delay-100 ease-in-out"
              :class="open ? 'rotate-180' : ''" />
      </button>
    </div>

    <Transition @enter="toggleStyle($event, true)"
                @after-enter="resetStyle"
                @leave="toggleStyle($event, false)"
                @after-leave="resetStyle">
      <div v-show="open"
           :id="panelId"
           class="p-2"
           role="region"
           :aria-labelledby="headerId">
        <slot />
      </div>
    </Transition>
  </div>
</template>
