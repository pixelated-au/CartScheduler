<script setup>
    import CartReservationManagement from '@/Components/CartReservationManagement.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import FilledShiftsChart from '@/Pages/Admin/Dashboard/FilledShiftsChart.vue'
    import Tags from '@/Pages/Admin/Dashboard/Tags.vue'
    import { Inertia } from '@inertiajs/inertia'

    const props = defineProps({
        totalUsers: Number,
        totalLocations: Number,
        shiftFilledData: Array,
        outstandingReports: Array,
    })

    const go = url => Inertia.visit(url)
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Admin Dashboard</h2>
        </template>

        <div class="py-2 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-xl sm:rounded-lg">

                    <div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 m-6">
                        <div class="cursor-pointer bg-gray-100 dark:bg-slate-700 p-6 rounded-lg shadow-lg hover:bg-gray-200 dark:hover:bg-slate-800 transition-colors"
                             @click="go(route('admin.reports.index'))">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                <span class="text-gray-600 dark:text-gray-300">{{ outstandingReports.length }}</span>
                                <span class="text-gray-600 dark:text-gray-300">
                                    Incomplete Reports
                                </span>
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300">
                                The number of reports that participants are yet to submit. </p>
                        </div>
                        <div class="cursor-pointer bg-gray-100 dark:bg-slate-700 p-6 rounded-lg shadow-lg hover:bg-gray-200 dark:hover:bg-slate-800 transition-colors"
                             @click="go(route('admin.users.index'))">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">

                                <span class="text-gray-600 dark:text-gray-300">{{ totalUsers }}</span>
                                Users </h3>
                            <p class="text-gray-700 dark:text-gray-300">Total number of users in the system.</p>
                        </div>
                        <div class="cursor-pointer bg-gray-100 dark:bg-slate-700 p-6 rounded-lg shadow-lg hover:bg-gray-200 dark:hover:bg-slate-800 transition-colors"
                             @click="go(route('admin.locations.index'))">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                <span class="text-gray-600 dark:text-gray-300">{{ totalLocations }}</span>
                                <span class="text-gray-600 dark:text-gray-300 ml-1">Locations</span>
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300">Total number of locations in the system.</p>
                        </div>
                        <div class="dashboard col-span-full sm:p-6 rounded-lg shadow-lg grid grid-cols-1 bg-white dark:bg-slate-900">
                            <CartReservationManagement/>
                        </div>
                        <div class="col-span-full bg-gray-100 dark:bg-slate-700 p-6 rounded-lg shadow-lg grid grid-cols-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                <span class="text-gray-600 dark:text-gray-300">Filled Shifts</span>
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300">For the next 14 days.</p>
                            <FilledShiftsChart :shiftData="shiftFilledData"/>
                        </div>
                        <Tags/>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.shifts-chart {
    min-height: 300px;
}
</style>
