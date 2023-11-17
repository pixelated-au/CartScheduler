<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import JetSectionBorder from '@/Jetstream/SectionBorder.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import ShowRegularAvailabilityForm from "@/Pages/Profile/Partials/ShowRegularAvailabilityForm.vue";
import ShowVacationsAvailabilityForm from "@/Pages/Profile/Partials/ShowVacationsAvailabilityForm.vue";
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import {onMounted} from "vue";

defineProps({
    vacations: {
        type: Object,
        required: false,
    },
    availability: {
        type: Object,
        required: false,
    },
});

onMounted(() => {
    axios.post(route('set.viewed-availability'))
})
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div>
                    <ShowVacationsAvailabilityForm :vacations="vacations.data"  class="mt-10 sm:mt-0" />
                    <JetSectionBorder />
                    <ShowRegularAvailabilityForm :availability="availability.data"  class="mt-10 sm:mt-0" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
