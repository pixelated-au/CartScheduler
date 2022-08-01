<script setup>
    import DataTable from '@/Components/DataTable.vue'
    import JetHelpText from '@/Jetstream/HelpText.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import Modal from '@/Jetstream/Modal.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import UserProfileForm from '@/Pages/Admin/Users/Partials/UserProfileForm.vue'
    import { computed, ref } from 'vue'

    defineProps({
        users: Array,
        availableRoles: Array,
        permissions: Object,
    })

    const headers = [
        {
            text: 'ID',
            value: 'id',
            sortable: true,
            width: '10%',
        },
        {
            text: 'Name',
            value: 'name',
            sortable: true,
            width: '20%',
        },
        {
            text: 'Email',
            value: 'email',
            sortable: true,
            width: '20%',
        },
        {
            text: 'Phone',
            value: 'phone',
            sortable: true,
            width: '20%',
        },
        {
            text: 'Gender',
            value: 'gender',
            sortable: true,
            width: '20%',
        },
        {
            text: 'Active',
            value: 'is_active',
            sortable: true,
            width: '20%',
        },
        {
            text: 'Role',
            value: 'role',
            sortable: true,
            width: '20%',
        },
    ]

    const userSearch = ref('')
    const selectedUser = ref(null)

    const handleSelection = (selection) => {
        selectedUser.value = selection
    }

    const showModal = computed(() => {
        return !!selectedUser.value
    })
</script>

<template>
    <AppLayout title="Team Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
            <h1 class="font-semibold text-5xl text-fuchsia-600 leading-tight">TODO SEARCH AND FILTERS</h1>
        </template>

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg sm:p-6">
                <JetLabel for="search" value="Search for a user"/>
                <JetInput id="search" v-model="userSearch" type="text" class="mt-1 block w-full"/>
                <JetHelpText>Search on name, email or phone</JetHelpText>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-10 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg sm:p-6">
                <data-table :headers="headers" :items="users" :search-value="userSearch" @click-row="handleSelection">
                    <template #item-email="{ email }">
                        <a :href="`mailto:${email}`" class="underline decoration-1">{{ email }}</a>
                    </template>
                    <template #item-phone="{ mobile_phone }">
                        <a :href="`tel:${mobile_phone}`" class="underline decoration-1">{{ mobile_phone }}</a>
                    </template>
                    <template #item-is_active="{ is_active }">
                        {{ is_active ? 'Yes' : 'No' }}
                    </template>
                </data-table>
            </div>
        </div>

        <modal :show="showModal" v-if="selectedUser" @close="selectedUser = null">
            <div class="p-5 bg-gray-200">
                <!--                <h3 class="font-semibold text-xl text-gray-800 leading-tight">{{ selectedUser.name }} </h3>-->
                <UserProfileForm :user="selectedUser"/>

            </div>
        </modal>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
