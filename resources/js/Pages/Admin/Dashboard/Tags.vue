<script setup lang="ts">
import axios from "axios";
import { useConfirm } from "primevue";
import { onMounted, ref } from "vue";
import { HandleDirective as vHandle, SlickItem, SlickList } from "vue-slicksort";
import useToast from "@/Composables/useToast";
import ConfirmationModal from "@/Jetstream/ConfirmationModal.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";

const allTags = ref<App.Data.ReportTagData[]>([]);

const confirm = useConfirm();

const getAllTags = async () => {
  const response = await axios.get<App.Data.ReportTagData[]>(route("admin.report-tags.index"));
  allTags.value = response.data;
};

onMounted(() => getAllTags());

const initTag = () => ({
  name: "",
  sort: 0,
});

const currentTag = ref<App.Data.ReportTagData>(initTag());

const toast = useToast();

const doSave = async () => {
  if (!currentTag.value?.name?.trim()) {
    toast.error("Tag name cannot be empty");
    return;
  }

  if (currentTag.value?.id) {
    await axios.put(route("admin.report-tags.update", currentTag.value.id), currentTag.value);
    toast.success("Tag updated successfully", undefined, { group: "bottom" });
  } else {
    await axios.post(route("admin.report-tags.store"), currentTag.value);
    toast.success("Tag added successfully", undefined, { group: "bottom" });
  }
  currentTag.value = initTag();
  await getAllTags();
};

const selectTag = (tag: App.Data.ReportTagData) => {
  currentTag.value = tag;
};

const confirmDelete = (event: Event) => {
  confirm.require({
    target: event.currentTarget as HTMLElement,
    message: "Are you sure you want to delete this tag?",
    header: "Confirm Deletion",
    icon: "iconify mdi--alert-circle-outline text-xl",
    acceptProps: {
      label: "Yes",
      severity: "danger",
      variant: "outlined",
    },
    rejectProps: {
      label: "No",
      severity: "primary",
    },
    accept: () => deleteTag(),
  });
};

const showDeleteModal = ref(false);

const deleteTag = async () => {
  if (!currentTag.value?.id) {
    return;
  }
  await axios.delete(route("admin.report-tags.destroy", currentTag.value.id));
  toast.success("Tag deleted successfully", undefined, { group: "bottom" });

  currentTag.value = initTag();
  showDeleteModal.value = false;
  await getAllTags();
};

const doSort = async () => {
  await axios.put(route("admin.report-tag.sort-order"), { ids: allTags.value.map((tag) => tag.id) });
  toast.success("The tags have been reordered", undefined, { group: "bottom" });
};
</script>

<template>
  <div class="col-span-full bg-sub-panel dark:bg-sub-panel-dark p-6 rounded-lg border border-transparent grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <div>
      <h3 class="text-lg font-semibold">
        <span>{{ allTags?.length }}</span>
        <span class="ml-1">Report Tags</span>
      </h3>
      <p class="text-sm"><em>Used for quickly filling out reports.</em></p>
    </div>
    <div v-if="allTags?.length" class="px-3">
      <SlickList axis="xy" v-model:list="allTags" @sort-end="doSort">
        <SlickItem v-for="(tag, i) in allTags"
                   :key="tag.id"
                   :index="i"
                   class="inline-flex items-center mr-2 mb-2 border border-1 border-gray-400 dark:border-gray-500 rounded">
          <div v-handle class="px-1 cursor-grab iconify mdi--dots-vertical"></div>
          <button class="px-2 py-1 focus:outline-none focus:border-gray-900 dark:focus:border-gray-200 focus:ring focus:ring-gray-300 dark:focus:ring-gray-700 leading-none hover:bg-gray-600 active:bg-gray-900 dark:hover:bg-gray-400 dark:active:bg-gray-200"
                  @click="selectTag(tag)">
            {{ tag.name }}
          </button>
        </SlickItem>
      </SlickList>
    </div>
    <div class="px-6">
      <h4 v-if="!currentTag?.id">Add a new Tag</h4>
      <h4 v-else>Edit Tag</h4>
      <JetLabel for="name" value="Name" />
      <form class="flex flex-wrap" @submit.prevent="doSave">
        <JetInput id="name" v-model="currentTag.name" type="text" class="w-full mb-3" />
        <div class="flex justify-between w-full">
          <PButton v-if="currentTag?.id"
                   icon="iconify mdi--trash-can-outline"
                   outline
                   type="button"
                   severity="danger"
                   @click.prevent="confirmDelete" />
          <div class="flex">
            <PButton v-if="currentTag?.id"
                     class="mr-3"
                     type="button"
                     style-type="primary"
                     @click.prevent="doSave">
              Save
            </PButton>
            <PButton v-else class="" type="button" style-type="primary" @click.prevent="doSave">
              Add
            </PButton>
            <PButton v-if="currentTag?.id"
                     outline
                     icon="iconify mdi--close-circle-outline"
                     type="button"
                     severity="secondary"
                     @click.prevent="currentTag = initTag()" />
          </div>
        </div>
      </form>
    </div>
  </div>
  <PConfirmPopup />

  <ConfirmationModal :show="showDeleteModal">
    <template #title>
      Delete Tag
    </template>

    <template #content>
      <p>Are you sure you want to delete this tag?</p>
    </template>

    <template #footer>
      <PButton class="mr-3" type="button" style-type="primary" @click.prevent="deleteTag">
        Delete
      </PButton>
      <PButton outline type="button" style-type="secondary" @click.prevent="showDeleteModal = false">
        Cancel
      </PButton>
    </template>
  </ConfirmationModal>
</template>
