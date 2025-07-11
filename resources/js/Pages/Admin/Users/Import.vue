<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { computed, inject } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({});

const route = inject("route");

const listRouteAction = () => {
  router.visit(route("admin.users.index"));
};

const form = useForm<{ file: File | null }>({
  file: null,
});

const uploadFile = () => {
  form.post(route("admin.users.import.import"), {
    preserveScroll: true,
  });
};

const validationErrors = computed(() => {
  if (form.errors && Object.keys(form.errors).length === 0 && Object.getPrototypeOf(form.errors) === Object.prototype) {
    return [];
  }

  const errors = [];
  for (const errorsKey in form.errors) {
    const error = form.errors[errorsKey as keyof typeof form.errors];
    errors.push(error);
  }

  return errors;
});

function handleFileChange(event: Event) {
  const inputElement = event.target as HTMLInputElement;
  if (inputElement.files) {
    form.file = inputElement.files?.[0] || null;
  }
}

const templateFile = route("admin.user-import-template");
</script>

<template>
  <AppLayout title="Import Users">
    <template #header>
      <div class="flex justify-between">
        <h2>Import Users</h2>
        <PButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
          Back
        </PButton>
      </div>
    </template>

    <div class="pt-10 mx-auto max-w-7xl sm:px-6">
      <div v-if="validationErrors.length"
           class="overflow-auto px-5 py-3 mb-6 text-white rounded bg-warning dark:bg-warning-light min-h-10">
        <h4>
          Oops! it seems you have
          {{ validationErrors.length === 1 ? 'a problem' : 'some problems' }}
          with the spreadsheet you uploaded:
        </h4>
        <ul class="formatted-list">
          <li v-for="error in validationErrors" :key="error">
            {{ error }}
          </li>
        </ul>
      </div>

      <div class="flex flex-col px-4 py-3 rounded bg-sub-panel dark:bg-sub-panel-dark std-border">
        <form class="flex flex-col w-full" @submit.prevent="uploadFile">
          <div class="flex flex-col gap-3 w-full">
            <div class="flex flex-col gap-3">
              <h3>User Import</h3>
              <div class="px-3 py-6 rounded border std-border">
                <ol class="formatted-list">
                  <li>Download and open the <a :href="templateFile">Template Excel file</a></li>
                  <li>
                    <strong>Closely</strong> follow the instructions that appear at the top of the Excel file.
                    <ul>
                      <li>
                        You can copy and past data from another source into the excel file but please make sure the data
                        conforms to the instructions.
                      </li>
                      <li>
                        If the importer finds an email address that already belongs to a user in the system, it will
                        update the data for that user. Otherwise, it will create a new user.
                      </li>
                      <li>
                        Note, if you wish to update a users' email address, you'll need to do that manually by editing
                        that user on this site.
                      </li>
                    </ul>
                  </li>
                  <li>
                    Upload the file. If there are any issues, you will be notified on what the issues are and what needs
                    fixing.
                    <ul>
                      <li>If there are issues, follow the instructions, fix them and re-upload.</li>
                      <li>Note, you may need to reload this page in order for the upload to work again.</li>
                    </ul>
                  </li>
                </ol>
              </div>
            </div>

            <div class="flex flex-col">
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="file-input">
                Upload spreadsheet.<br>
              </label>

              <input class="block w-full text-sm italic rounded border cursor-pointer bg-text-input dark:bg-text-input-dark
                        p-1 std-border hover:bg-gray-200 ease-in-out transition-colors file:text-sm
                        file:py-2 file:px-3 file:rounded file:border-0 file:bg-neutral-300 file:text-neutral-700 dark:file:bg-neutral-500 dark:file:hover:bg-neutral-600 dark:file:text-gray-200
                      file:hover:ring-primary dark:file:hover:ring-primary-light file:hover:ring-1 file:mr-3 file:cursor-pointer  file:hover:scale-[103%]
                      file:hover:bg-gray-100 file:ease-in-out file:transition-[background-color,transform,box-shadow]"
                     aria-describedby="file-input-help"
                     id="file-input"
                     type="file"
                     accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                     @input="handleFileChange">

              <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file-input-help">
                XLSX, XLS or CSV files only
              </div>
              <div class="mt-1 text-sm text-blue-500 underline dark:text-gray-300"
                   id="file-input-help">
                <a :href="templateFile">Template Excel file</a>
              </div>
            </div>
          </div>
          <div class="flex justify-end mt-3 w-full">
            <SubmitButton type="submit"
                          :label="form.file ? 'Upload and Import' : 'Select a file above to upload'"
                          :disabled="!form.file"
                          :processing="form.processing"
                          :success="form.wasSuccessful"
                          :failure="form.hasErrors"
                          :errors="form.errors" />
          </div>
          <div class="w-full">
            <progress v-if="form.progress" :value="form.progress.percentage" max="100">
              {{ form.progress.percentage }}%
            </progress>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
