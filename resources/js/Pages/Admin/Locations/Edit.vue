<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import LocationForm from '@/Pages/Admin/Locations/Partials/LocationForm.vue'
    import { Inertia } from '@inertiajs/inertia'

    defineProps({
        location: Object,
    })

    const listRouteAction = () => {
        Inertia.visit(route('admin.locations.index'))
    }
</script>

<template>
    <AppLayout :title="`Location: ${location.data.name}`">
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Location {{ location.data.name }}</h2>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <LocationForm :location="location"/>
            </div>
        </div>
    </AppLayout>
</template>
