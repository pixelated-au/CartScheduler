<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import UserProfileForm from '@/Pages/Admin/Users/Partials/UserProfileForm.vue'
    import { Inertia } from '@inertiajs/inertia'

    defineProps({
        availableRoles: Array,
        permissions: Object,
    })

    const listRouteAction = () => {
        Inertia.visit(route('admin.users.index'))
    }
</script>

<template>
    <AppLayout title="Create a new User">
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New User</h2>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <UserProfileForm :user="{}" action="add"/>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
