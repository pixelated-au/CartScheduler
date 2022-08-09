<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import LocationForm from '@/Pages/Admin/Locations/Partials/LocationForm.vue'

    defineProps({})

</script>

<template>
    <AppLayout title="Create a new Location">
        <template #header>
            <h1 class="font-semibold text-2xl text-gray-800 leading-tight">New Location</h1>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <LocationForm :location="{data: {shifts: []}}" action="add"/>
            </div>
        </div>
    </AppLayout>
</template>
