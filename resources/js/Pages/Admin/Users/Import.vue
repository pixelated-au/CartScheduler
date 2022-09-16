<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { useForm } from '@inertiajs/inertia-vue3'
    import { computed } from 'vue'

    defineProps({
        exampleFile: String,
    })

    const listRouteAction = () => {
        Inertia.visit(route('admin.users.index'))
    }

    const form = useForm({
        file: null,
    })

    const uploadFile = () => {
        form.post(route('admin.users.import.import'), {
            preserveScroll: true,
        })
    }

    const hasErrors = computed(() =>
        !form.errors || Object.keys(form.errors).length > 0 || Object.getPrototypeOf(form.errors) !== Object.prototype,
    )

    const validationErrors = computed(() => {
        if (form.errors && Object.keys(form.errors).length === 0 && Object.getPrototypeOf(form.errors) === Object.prototype) {
            return []
        }

        const errors = []
        for (const errorsKey in form.errors) {
            if (Object.prototype.hasOwnProperty.call(form.errors, errorsKey)) {
                const error = form.errors[errorsKey]
                errors.push(error)
            }
        }
        return errors
    })
</script>

<template>
    <AppLayout title="Import Users">
        <template #header>
            <div class="flex justify-between">
                <h1 class="font-semibold text-2xl text-gray-800 leading-tight">Import Users</h1>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div v-if="validationErrors.length" class="bg-red-200 p-3 mb-6 rounded-lg h-36 overflow-auto shadow-lg">
                <h3 class="mb-3">
                    There are {{ validationErrors.length }} problems with the spreadsheet you uploaded 🤭 </h3>
                <ul class="pl-3">
                    <li v-for="error in validationErrors" :key="error">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <div class="flex items-center justify-start px-4 py-3 bg-gray-50 dark:bg-gray-900 px-6 shadow rounded-lg rounded-lg">
                <form class="w-full" @submit.prevent="uploadFile">
                    <div class="w-full">
                        <div class="flex items-center">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                                   for="file-input">
                                Upload spreadsheet.<br>
                                <strong>Note, this will email every user a link to log in!</strong>
                            </label>
                        </div>
                        <input class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                               aria-describedby="file-input-help"
                               id="file-input"
                               type="file"
                               accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                               @input="form.file = $event.target.files[0]">
                    </div>
                    <div class="w-full flex justify-between mt-3">
                        <div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file-input-help">
                                XLSX, XLS or CSV files only
                            </div>
                            <div class="mt-1 text-sm text-blue-500 underline dark:text-gray-300 dark:text-blue-300"
                                 id="file-input-help">
                                <a :href="exampleFile">Example Excel file</a>
                            </div>
                        </div>
                        <JetButton type="submit"
                                   style-type="primary"
                                   @click.prevent="uploadFile"
                                   :disabled="!form.file">
                            <template v-if="form.file">Upload and Import</template>
                            <template v-else>Select a file to upload</template>
                        </JetButton>
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
