<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { truncate } from 'lodash'

    defineProps({
        locations: Object.data,
    })

    const onNewLocation = () => {
        Inertia.visit(route('admin.locations.create'))
    }

    const locationClicked = (location) => {
        Inertia.visit(route('admin.locations.edit', { id: location.id }))
    }

    const truncateDescription = description => truncate(description, {
        length: 100,
        omission: '...',
        separator: ' ',
    })
</script>

<template>
    <AppLayout title="Locations">
        <template #header>
            <div class="flex justify-between">
                <h1 class="font-semibold text-2xl text-gray-800 leading-tight">Locations</h1>
                <JetButton class="mx-3" style-type="primary" @click="onNewLocation">
                    New Location
                </JetButton>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 sm:gap-4">
            <div v-for="location in locations.data"
                 :key="location.id"
                 class="bg-white shadow-xl sm:rounded-lg sm:p-6 cursor-pointer hover:bg-violet-100 hover:transition-colors"
                 @click="locationClicked(location)">
                <div class="flex flex-col justify-between h-full">
                    <div>
                        <h4 class="font-semibold">{{ location.name }}</h4>
                        <div>{{ truncateDescription(location.clean_description) }}</div>
                    </div>
                    <div>
                        <div class="border-t border-gray-100 mt-3 pt-3"></div>
                        <h5 class="font-semibold text-center">Shifts</h5>
                        <div class="text-center text-4xl">{{ location.shifts.length }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
