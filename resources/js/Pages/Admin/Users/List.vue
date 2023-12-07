<script setup>
    import DataTable from '@/Components/DataTable.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetHelpText from '@/Jetstream/HelpText.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { ref } from 'vue'
    import headers from './Lib/UserDataTableHeaders'

    defineProps({
        users: Object,
        availableRoles: Array,
        permissions: Object,
    })

    const userSearch = ref('')

    const onNewUser = () => {
        Inertia.visit(route('admin.users.create'))
    }

    const onImportUsers = () => {
        Inertia.visit(route('admin.users.import.show'))
    }

    const handleSelection = (selection) => {
        Inertia.visit(route('admin.users.edit', selection.id))
    }
</script>

<template>
    <AppLayout title="Users">
        <template #header>
            <div class="flex justify-between flex-wrap md:flex-nowrap">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight w-full md:w-auto">
                    Users</h2>
                <div class="w-full md:w-auto">
                    <JetButton class="hidden sm:inline-flex md:mx-3 w-full md:w-auto my-3 md:my-0"
                               style-type="secondary"
                               outline
                               @click="onImportUsers">
                        Import Users
                    </JetButton>
                    <JetButton class="py-4 sm:py-2 md:mx-3 w-full md:w-auto" style-type="primary" @click="onNewUser">
                        New User
                    </JetButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto sm:pt-10 sm:pb-5 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-900 shadow-xl sm:rounded-lg py-3 px-4 sm:p-6">
                <JetLabel for="search" value="Search for a user:"/>
                <!-- Overriding background colours for usability -->
                <JetInput id="search" v-model="userSearch" type="text" class="mt-1 block w-full dark:bg-slate-700 sm:dark:bg-slate-800"/>
                <JetHelpText>Search on name, email, phone, role or any field</JetHelpText>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-10 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-900 shadow-xl sm:rounded-lg sm:p-6">
                <data-table :headers="headers"
                            :items="users.data"
                            :search-value="userSearch"
                            @click-row="handleSelection">
                    <template #item-email="{ email }">
                        <a :href="`mailto:${email}`">{{ email }}</a>
                    </template>
                    <template #item-phone="{ mobile_phone }">
                        <a :href="`tel:${mobile_phone}`">{{ mobile_phone }}</a>
                    </template>
                    <template #item-is_enabled="{ is_enabled }">
                        <div v-if="is_enabled">Yes</div>
                        <div v-else class="text-red-600 dark:text-red-400">No</div>
                    </template>
                    <template #item-is_unrestricted="{ is_unrestricted }">
                        <div v-if="!is_unrestricted" class="text-red-600 dark:text-red-400">Yes</div>
                        <div v-else>No</div>
                    </template>
                </data-table>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
