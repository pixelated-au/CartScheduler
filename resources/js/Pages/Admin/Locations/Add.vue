<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import LocationForm from '@/Pages/Admin/Locations/Partials/LocationForm.vue'
    import { Inertia } from '@inertiajs/inertia'

    const listRouteAction = () => {
        Inertia.visit(route('admin.locations.index'))
    }
    const newLocation = {
        data: {
            name: '',
            description: '',
            min_volunteers: 2,
            max_volunteers: 3,
            requires_brother: true,
            is_enabled: true,
            shifts: [],
        },
    }
</script>

<template>
    <AppLayout title="Create a new Location">
        <template #header>
            <div class="flex justify-between">
                <h1 class="font-semibold text-2xl text-gray-800 leading-tight">New Location</h1>
                <JetButton class="mx-3" type="button" style-type="secondary" outline @click.prevent="listRouteAction">
                    Back
                </JetButton>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <LocationForm :location="newLocation" action="add"/>
            </div>
        </div>
    </AppLayout>
</template>
