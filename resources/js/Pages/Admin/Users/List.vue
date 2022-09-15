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
            <div class="flex justify-between">
                <h1 class="font-semibold text-2xl text-gray-800 leading-tight">Users</h1>
                <div>
                    <JetButton class="mx-3" style-type="secondary" outline @click="onImportUsers">
                        Import Users
                    </JetButton>
                    <JetButton class="mx-3" style-type="primary" @click="onNewUser">
                        New User
                    </JetButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg sm:p-6">
                <JetLabel for="search" value="Search for a user"/>
                <JetInput id="search" v-model="userSearch" type="text" class="mt-1 block w-full"/>
                <JetHelpText>Search on name, email, phone, role or any field</JetHelpText>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-10 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg sm:p-6">
                <data-table :headers="headers"
                            :items="users.data"
                            :search-value="userSearch"
                            @click-row="handleSelection">
                    <template #item-email="{ email }">
                        <a :href="`mailto:${email}`" class="underline decoration-1">{{ email }}</a>
                    </template>
                    <template #item-phone="{ mobile_phone }">
                        <a :href="`tel:${mobile_phone}`" class="underline decoration-1">{{ mobile_phone }}</a>
                    </template>
                    <template #item-is_enabled="{ is_enabled }">
                        {{ is_enabled ? 'Yes' : 'No' }}
                    </template>
                </data-table>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
