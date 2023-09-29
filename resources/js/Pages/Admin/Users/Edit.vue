<script setup>
import JetButton from '@/Jetstream/Button.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import UserProfileForm from '@/Pages/Admin/Users/Partials/UserProfileForm.vue'
import ShowRegularAvailabilityForm from "@/Pages/Profile/Partials/ShowRegularAvailabilityForm.vue";
import ShowVacationsAvailabilityForm from "@/Pages/Profile/Partials/ShowVacationsAvailabilityForm.vue";
import {Inertia} from '@inertiajs/inertia'

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
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    User {{ editUser.name }} </h2>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
                <UserProfileForm :user="editUser"/>
            </div>
            <template v-if="$page.props.enableUserAvailability">
                <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
                    <ShowVacationsAvailabilityForm :userId="editUser.id" :vacations="editUser.vacations"
                                                   class="mt-10 sm:mt-0"/>
                </div>
                <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8">
                    <ShowRegularAvailabilityForm :userId="editUser.id" :availability="editUser.availability"
                                                 class="mt-10 sm:mt-0"/>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
