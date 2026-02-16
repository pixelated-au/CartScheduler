<script setup lang="ts" generic="AllowedModelValues">
import { computed, inject, onMounted, onUnmounted, ref, useId, useTemplateRef } from "vue";
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

const panelHeight = ref("0");

const setHeight = (el: Element, isOpening: boolean) => {
  const element = el as HTMLElement;
  console.log(element.scrollHeight);
  panelHeight.value = isOpening ? `${element.scrollHeight}px` : "0px";
  console.log(panelHeight.value);
};
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

    <Transition @enter="setHeight($event,true)"
                @leave="setHeight($event,false)">
      <div v-show="open"
           :id="panelId"
           :style="`--panel-height: ${panelHeight}`"
           class="transition-[height] duration-500 h-[var(--panel-height)]"
           role="region"
           :aria-labelledby="headerId">
        <div class="p-2">
          <!-- Nested is needed to prevent the panel from collapsing when the content is removed -->
          <slot />
        </div>
      </div>
    </Transition>
  </div>
</template>

<!--suppress CssUnusedSymbol -->
<style lang="css" scoped>
.v-enter-active,
.v-leave-active {
  overflow: hidden;
}
</style>
