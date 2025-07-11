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
    if (Object.prototype.hasOwnProperty.call(form.errors, errorsKey)) {
      const error = form.errors[errorsKey];
      errors.push(error);
    }
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
        <h1 class="font-semibold text-2xl text-gray-800 leading-tight">Import Users</h1>
        <PButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
          Back
        </PButton>
      </div>
    </template>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <div v-if="validationErrors.length" class="bg-red-200 p-3 mb-6 rounded-lg h-36 overflow-auto shadow-lg">
        <h3 class="mb-3">
          There are {{ validationErrors.length }} problems with the spreadsheet you uploaded 🤭
        </h3>
        <ul class="pl-3">
          <li v-for="error in validationErrors" :key="error">
            {{ error }}
          </li>
        </ul>
      </div>

      <div class="flex items-center justify-start px-4 py-3 bg-gray-50 dark:bg-gray-900 shadow rounded-lg">
        <form class="w-full" @submit.prevent="uploadFile">
          <div class="w-full">
            <div class="text-gray-900 dark:text-gray-300 mb-5">
              <h3>User Import</h3>
              <div
                  class="my-5 p-10 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded">
                <ol class="ml-3 list-decimal list-outside">
                  <li class="mb-4">
                    Download and open the <a :href="templateFile">
                      Template Excel
                      file
                    </a>
                  </li>
                  <li class="mb-4">
                    <strong>Closely</strong> follow the instructions that appear at the
                    top of the Excel file.
                    <ul class="pl-4 list-disc list-outside">
                      <li class="mb-2">
                        You can copy and past data from another source into the
                        excel file but please make sure the data conforms to the instructions.
                      </li>
                      <li class="mb-2">
                        If the importer finds an email address that already belongs
                        to a user in the system, it will update the data for that user.
                        Otherwise, it will create a new user
                      </li>
                      <li class="mb-2">
                        Note, if you wish to update a users' email address, you'll
                        need to do that manually by editing that user on this site.
                      </li>
                    </ul>
                  </li>
                  <li class="mb-4">
                    Upload the file. If there are any issues, you will be notified on
                    what the issues are and what needs fixing.
                    <ul class="pl-4 list-disc list-outside">
                      <li class="mb-2">
                        If there are issues, follow the instructions, fix them and
                        re-upload.
                      </li>
                      <li class="mb-2">
                        Note, you may need to reload this page in order for the
                        upload to work again
                      </li>
                    </ul>
                  </li>
                </ol>
              </div>
            </div>
            <div class="flex items-center">
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="file-input">
                Upload spreadsheet.<br>
              </label>
            </div>

            <input class="block w-full text-sm italic rounded-md border cursor-pointer bg-panel dark:bg-panel-dark
                        p-1 focus:outline-none std-border hover:bg-gray-200 ease-in-out transition-colors file:text-sm
                        file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-gray-200 file:text-gray-700
                      file:hover:ring-primary file:hover:ring-1 file:mr-3 file:cursor-pointer  file:hover:scale-[103%]
                      file:hover:bg-gray-100 file:ease-in-out file:transition-[background-color,transform,box-shadow]"
                   aria-describedby="file-input-help"
                   id="file-input"
                   type="file"
                   accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                   @input="handleFileChange">
          </div>
          <div class="w-full flex justify-between mt-3">
            <div>
              <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file-input-help">
                XLSX, XLS or CSV files only
              </div>
              <div class="mt-1 text-sm text-blue-500 underline dark:text-gray-300"
                   id="file-input-help">
                <a :href="templateFile">Template Excel file</a>
              </div>
            </div>
            <SubmitButton type="submit"
                          :label="form.file ? 'Upload and Import' : 'Select a file to upload'"
                          :disabled="!form.file"
                          :processing="form.processing"
                          :success="form.wasSuccessful"
                          :failure="form.hasErrors"
                          :errors="form.errors"/>
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
