<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Tags from '@/Pages/Admin/Dashboard/Tags.vue'
    import { Link } from '@inertiajs/inertia-vue3'

    defineProps({
        totalUsers: Number,
        totalLocations: Number,
    })
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                    <div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 m-6">
                        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <span class="text-gray-600">{{ totalUsers }}</span>

                                <Link :href="route('admin.users.index')">
                                    Users
                                </Link>
                            </h3>
                            <p class="text-gray-700">
                                Total number of users in the system. </p>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <span class="text-gray-600">{{ totalLocations }}</span>
                                <span class="text-gray-500 ml-1">Locations</span>
                            </h3>
                            <p class="text-gray-700">
                                Total number of locations in the system. </p>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <span class="text-gray-600">44</span>
                                <!--                                <span class="text-gray-600">{{ totalOrders }}</span>-->
                                <span class="text-gray-500 ml-1">Empty Shifts</span>
                            </h3>
                            <p class="text-gray-700">
                                Total number of orders in the system. </p>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <span class="text-gray-600">44</span>
                                <!--                                <span class="text-gray-600">{{ totalOrders }}</span>-->
                                <span class="text-gray-500 ml-1">Completed Reports</span>
                            </h3>
                            <p class="text-gray-700">
                                Total number of orders in the system. </p>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <span class="text-gray-600">44</span>
                                <!--                                <span class="text-gray-600">{{ totalOrders }}</span>-->
                                <span class="text-gray-500 ml-1">Announcements</span>
                            </h3>
                            <p class="text-gray-700">
                                Total number of orders in the system. </p>
                        </div>
                        <Tags/>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
