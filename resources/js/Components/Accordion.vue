<script setup lang="ts" generic="AllowedModelValues">
import { provide, ref } from "vue";
import { AccordionContext } from "@/Utils/provide-inject-keys";

type ModelValueArray = Array<AllowedModelValues>;
type ModelValue = ModelValueArray | AllowedModelValues | undefined;

const { multiple = false } = defineProps<{
  multiple?: boolean;
}>();

const emit = defineEmits<{
  transitionStarted: [void];
  transitionFinished: [void];
}>();

const modelValue = defineModel<ModelValue>({ default: [], required: false });

const panelCount = ref(0);
const headerRefs = ref<Array<HTMLElement | undefined>>([]);

const isOpen: AccordionContext<AllowedModelValues>["isOpen"] = (value) => {
  const current = modelValue.value;
  if (!multiple) {
    return current === value;
  }

  return Array.isArray(current) ? current.includes(value) : false;
};

const toggle = (index: AllowedModelValues) => {
  if (!multiple) {
    (modelValue.value as Partial<ModelValue>) = isOpen(index) ? undefined : index;
    return;
  }

  const i = (modelValue.value as ModelValueArray).indexOf(index);
  if (i === -1) {
    (modelValue.value as ModelValueArray).push(index);
  } else {
    (modelValue.value as ModelValueArray).splice(i, 1);
  }
};

const registerPanel = () => {
  const index = panelCount.value++;
  // ensure headerRefs has a slot for this index
  if (headerRefs.value.length <= index) headerRefs.value.length = index + 1;
  return index;
};

const setHeaderRef = (index: number, el: HTMLElement | null) => {
  headerRefs.value[index] = el ?? undefined;
};

const focusIndex = (index: number) => {
  const count = panelCount.value;
  if (!count) return;
  for (let i = 0; i < count; i++) {
    const next = (index + i) % count;
    const el = headerRefs.value[next];
    // Skip missing or disabled headers
    if (el && !(el as HTMLButtonElement).disabled) {
      el.focus();
      return;
    }
  }
};

const onHeaderKeydown = (e: KeyboardEvent, index: number) => {
  const count = panelCount.value;
  if (!count) return;
  switch (e.key) {
    case "ArrowDown":
    case "Down":
      e.preventDefault();
      focusIndex((index + 1) % count);
      break;
    case "ArrowUp":
    case "Up":
      e.preventDefault();
      focusIndex((index - 1 + count) % count);
      break;
    case "Home":
      e.preventDefault();
      focusIndex(0);
      break;
    case "End":
      e.preventDefault();
      focusIndex(count - 1);
      break;
  }
};

// Provide context for AccordionPanel
provide<AccordionContext<AllowedModelValues>>(AccordionContext, {
  registerPanel,
  isOpen,
  toggle,
  onHeaderKeydown,
  setHeaderRef,
});
</script>

<template>
  <div class="accordion">
    <slot />
  </div>
</template>
