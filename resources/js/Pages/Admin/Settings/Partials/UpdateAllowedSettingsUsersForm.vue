<script setup>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import {useForm} from '@inertiajs/inertia-vue3';
import axios from "axios";
import {onMounted, ref} from 'vue';

const props = defineProps({
    settings: Object,
})

const form = useForm({
    _method: 'PUT',
    allowedSettingsUsers: props.settings.allowedSettingsUsers,
})

const updateAllowedSettingsUsers = () => {
    form.put(route('admin.allowed-settings-users.update'), {
        errorBag: 'updateAllowedSettingsUsers',
        preserveScroll: true,
        onSuccess: () => form.defaults(),
    })
}

const adminUsers = ref()
onMounted(async () => {
    const response = await axios.get('/admin/admin-users')
    adminUsers.value = response.data.data
})

</script>

<template>
    <JetFormSection @submitted="updateAllowedSettingsUsers">
        <template #title>
            Settings Access
        </template>

        <template #description>
            Use this to determine which users are allowed to access the settings page.
        </template>

        <template #form>
            <div class="order-last sm:order-first col-span-6 sm:col-span-3 lg:col-span-2 bg-white rounded-lg shadow max-w-md dark:bg-gray-700">
                <ul class="overflow-y-auto text-sm text-gray-700 dark:text-gray-200"
                    aria-labelledby="admin-users">
                    <li v-for="user in adminUsers" :key="user.id">
                        <div class="px-3 flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <input :id="`admin-user-${user.id}`" type="checkbox" v-model="form.allowedSettingsUsers" :value="user.id"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label :for="`admin-user-${user.id}`"
                                   class="w-full ml-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">{{
                                    user.name
                                }}</label>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-span-6 sm:col-span-3 lg:col-span-4 mt-1 ml-1 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                <p class="mb-3">
                    Note: Not all admin users should necessarily have access to this page. Please only specify those who
                    know what impact changing these settings could have.
                </p>
                <p>
                    Some changes could have catastrophic effects on the application. Please be careful.
                </p>
            </div>
            <JetInputError :message="form.errors.allowedSettingsUsers" class="mt-2"/>

            <!--            <div class="col-span-6 sm:col-span-4">-->
            <!--                <JetLabel for="allowed_user_ids" value="IDs of users" />-->
            <!--                <JetInput id="allowed_user_ids" v-model="form.allowedSettingsUsers" type="text" class="mt-1 block w-full"/>-->
            <!--            </div>-->
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
