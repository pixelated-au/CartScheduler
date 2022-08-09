<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import LocationForm from '@/Pages/Admin/Locations/Partials/LocationForm.vue'

    defineProps({
        location: Object,
        availableRoles: Array,
        permissions: Object,
    })

</script>

<template>
    <AppLayout :title="`Location: ${location.name}`">
        <template #header>
            <h1 class="font-semibold text-2xl text-gray-800 leading-tight">Location {{ location.name }}</h1>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <LocationForm :location="location"/>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
