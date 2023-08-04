<script setup>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import {useForm} from '@inertiajs/inertia-vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    _method: 'PUT',
    siteName: props.settings.siteName,
});

const updateGeneralSettings = () => {
    form.put(route('admin.general-settings.update'), {
        errorBag: 'updateGeneralSettings',
        preserveScroll: true,
        onSuccess: () => form.defaults(),
    })
}
</script>

<template>
    <JetFormSection @submitted="updateGeneralSettings">
        <template #title>
            Site Settings
        </template>

        <template #description>
            These are the general settings for the web-app
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="site_name" value="Site Name"/>
                <JetInput id="site_name" v-model="form.siteName" type="text" class="mt-1 block w-full"/>
                <div class="mt-1 ml-1 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                    This will set the name of the site in the browser title bar.
                </div>
                <JetInputError :message="form.errors.siteName" class="mt-2"/>
            </div>
        </template>

        <template #actions>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </JetActionMessage>

            <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </JetButton>
        </template>
    </JetFormSection>
</template>
