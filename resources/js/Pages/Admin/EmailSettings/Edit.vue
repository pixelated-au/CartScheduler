<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import LocationForm from '@/Pages/Admin/Locations/Partials/LocationForm.vue';
import TextEditor from '@/Components/TextEditor.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import {useForm} from '@inertiajs/vue3';

const props = defineProps({
    emailReminderText: String,
});

const form = useForm({
    emailReminderText: props.emailReminderText,
});

const updateLocationData = () => {
    console.log(form.emailReminderText);

    form.put(route('admin.emailsettings.edit'),
        {
            preserveScroll: true,
        });
};
</script>

<template>
    <AppLayout title="Update shift reminder message">
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Update shift reminder message
                </h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <JetFormSection @submitted="updateLocationData">
                <template #title>
                    Update shift reminder message
                </template>

                <template #description>
                    Use this to change the shift reminder message for users.
                </template>

                <template #form>
                    <div class="col-span-6 sm:col-span-6 mb-3">
                        <JetLabel for="description" value="Description"/>
                        <p class="mb-1 text-sm italic text-gray-500 dark:text-gray-500">
                            NOTE: As part of standard HTML, empty paragraphs won't appear. A future update will have the ability to
                            create arbitrary spacing
                        </p>
                        <p class="mb-1 text-sm text-gray-700 dark:text-gray-300">
                            Return/Enter creates a new paragraph. Holding the Shift key while pressing Return/Enter will insert a line
                            break.
                        </p>
                        <TextEditor v-model="form.emailReminderText" highlight-syntax/>
                        <JetInputError :message="form.errors.description" class="mt-2"/>
                    </div>
                </template>

                <template #actions>
                    <div>
                  <PButton label="Save"
                           severity="primary"
                           class="mr-3"
                           :disabled="form.processing"
                           @click.prevent="updateLocationData" />
                              </div>
                </template>
            </JetFormSection>
        </div>
    </AppLayout>
</template>

<style lang="scss">
    @import 'vue3-easy-data-table/dist/style.css';
</style>
