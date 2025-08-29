<script setup lang="ts">
import { Dropdown } from "floating-vue";
import { inject, onMounted, ref } from "vue";
import BaseButton from "@/Components/EditorToolbar/BaseButton.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";

const editor = inject("editor");

const url = ref();
const doShow = ref(false);

const getPreviousLink = () => {
  const previousUrl = editor.value.getAttributes("link").href;
  url.value = previousUrl || "";
};

const setLink = () => {
  // empty
  if (url.value === "") {
    editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
    return;
  }

  // update link
  editor.value.chain().focus().extendMarkRange("link").setLink({ href: url.value }).run();
  doShow.value = false;
};

onMounted(() => {
  editor.value.on("selectionUpdate", (event) => {
    const currentUrl = event.editor.getAttributes("link").href;
    if (currentUrl) {
      getPreviousLink();
      doShow.value = true;
    }
  });
});
</script>

<template>
  <Dropdown @show="getPreviousLink" v-model:shown="doShow" class="inline-block">
    <BaseButton tooltip="Add Link">
      <svg xmlns="http://www.w3.org/2000/svg"
           viewBox="0 0 24 24"
           width="16"
           height="16"
           class="dark:fill-gray-100">
        <path fill="none" d="M0 0h24v24H0z"/>
        <path
            d="M18.364 15.536L16.95 14.12l1.414-1.414a5 5 0 1 0-7.071-7.071L9.879 7.05 8.464 5.636 9.88 4.222a7 7 0 0 1 9.9 9.9l-1.415 1.414zm-2.828 2.828l-1.415 1.414a7 7 0 0 1-9.9-9.9l1.415-1.414L7.05 9.88l-1.414 1.414a5 5 0 1 0 7.071 7.071l1.414-1.414 1.415 1.414zm-.708-10.607l1.415 1.415-7.071 7.07-1.415-1.414 7.071-7.07z"/>
      </svg>
    </BaseButton>

    <template #popper>
      <div class="flex items-center p-3 dark:bg-slate-900">
        <JetLabel for="url" value="Url:"/>
        <JetInput id="url" v-model="url" type="text" class="block mx-3" autocomplete="name"/>
        <PButton outline type="button" severity="success" @click.prevent="setLink">
          Ok
        </PButton>
        <PButton outline type="button" severity="secondary" @click.prevent="doShow = false" class="ml-3">
          X
        </PButton>
      </div>
    </template>
  </Dropdown>
</template>
