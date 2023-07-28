<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SoftwareUpdateForm from "@/Pages/Admin/Settings/Partials/SoftwareUpdateForm.vue";
import UpdateAllowedSettingsUsersForm from "@/Pages/Admin/Settings/Partials/UpdateAllowedSettingsUsersForm.vue";
import UpdateGeneralSettingsForm from "@/Pages/Admin/Settings/Partials/UpdateGeneralSettingsForm.vue";

defineProps({
    settings: Object,
})

</script>

<template>
    <AppLayout title="App Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Settings</h2>
        </template>

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-900 dark:text-white shadow-xl sm:rounded-lg sm:p-6 sm:mb-4">
                <ul>
                    <li v-for="(setting, name, idx) in settings" :key="idx">
                        {{ name }}: {{ setting }}
                    </li>
                </ul>
            </div>
            <UpdateGeneralSettingsForm :settings="settings" class="mb-10 md:mb-3"/>
            <UpdateAllowedSettingsUsersForm :settings="settings" class="mb-10 md:mb-3"/>
            <SoftwareUpdateForm :settings="settings"/>
        </div>
    </AppLayout>
</template>

<style scoped lang="scss">

</style>
