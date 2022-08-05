<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import JetHelpText from '@/Jetstream/HelpText.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { ref } from 'vue'

    defineProps({
        locations: Object.data,
    })

    const userSearch = ref('')

    const onNewLocation = () => {
        Inertia.visit(route('admin.users.create'))
    }

    const locationClicked = (location) => {
        Inertia.visit(route('admin.locations.edit', { id: location.id }))
    }
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

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg sm:p-6">
                <JetLabel for="search" value="Search for a user"/>
                <JetInput id="search" v-model="userSearch" type="text" class="mt-1 block w-full"/>
                <JetHelpText>Search on name, email, phone, role or any field</JetHelpText>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-10 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 sm:gap-4">
            <div v-for="location in locations.data"
                 :key="location.id"
                 class="bg-white shadow-xl sm:rounded-lg sm:p-6 cursor-pointer hover:bg-violet-100 hover:transition-colors"
                 @click="locationClicked(location)">
                <div class="flex flex-col justify-between h-full">
                    <div>
                        <h4 class="font-semibold">{{ location.name }}</h4>
                        <div>{{ location.description }}</div>
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
