<script setup>
    import Bell from '@/Components/Icons/Bell.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CartReservation from '@/Pages/Components/Dashboard/CartReservation.vue'
    import ReportsModal from '@/Pages/Components/Dashboard/ReportsModal.vue'
    import { computed, ref } from 'vue'

    defineProps({
        user: Object,
    })

    const outstandingReportCount = ref(0)

    const updateReportCount = count => outstandingReportCount.value = count

    const reportsLabel = computed(() => outstandingReportCount.value === 1 ? 'Report' : 'Reports')

    const showReportsModal = ref(false)
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col md:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
                <div>
                    <JetButton v-if="outstandingReportCount"
                               type="button"
                               style-type="danger"
                               class="mt-3 md:mt-0 py-3 w-full justify-center md:w-auto font-bold"
                               @click="showReportsModal = true">
                        <Bell color="#fff" class="mr-2"/>
                        {{ outstandingReportCount }} {{ reportsLabel }} Outstanding
                    </JetButton>
                </div>
            </div>
        </template>

        <div class="dashboard py-2 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg">
                    <CartReservation :user="user"/>
                </div>
            </div>
        </div>
    </AppLayout>
    <ReportsModal :show="showReportsModal"
                  @has-outstanding-reports="updateReportCount"
                  @close="showReportsModal = false"/>
</template>
