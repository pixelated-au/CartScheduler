<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import UserProfileForm from '@/Pages/Admin/Users/Partials/UserProfileForm.vue'
    import { Inertia } from '@inertiajs/inertia'

    defineProps({
        editUser: Object,
        availableRoles: Array,
        permissions: Object,
    })

    const listRouteAction = () => {
        Inertia.visit(route('admin.users.index'))
    }
</script>

<template>
    <AppLayout :title="`User: ${editUser.name}`">
        <template #header>
            <div class="flex justify-between">
                <h1 class="font-semibold text-2xl text-gray-800 leading-tight">User {{ editUser.name }}</h1>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <UserProfileForm :user="editUser"/>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
