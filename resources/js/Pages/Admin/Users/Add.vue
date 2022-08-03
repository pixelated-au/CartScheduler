<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import UserProfileForm from '@/Pages/Admin/Users/Partials/UserProfileForm.vue'

    defineProps({
        availableRoles: Array,
        permissions: Object,
    })

</script>

<template>
    <AppLayout title="Create a new User">
        <template #header>
            <h1 class="font-semibold text-2xl text-gray-800 leading-tight">New User</h1>
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
