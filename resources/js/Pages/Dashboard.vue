<script setup>
    import CartReservation from '@/Components/CartReservation.vue'
    import Bell from '@/Components/Icons/Bell.vue'
    import useToast from '@/Composables/useToast'
    import JetButton from '@/Jetstream/Button.vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { computed, onMounted, ref } from 'vue'

    defineProps({
        user: Object,
    })
    const toast = useToast()

    const outstandingReports = ref([])

    const getOutstandingReports = async () => {
        try {
            const response = await axios.get('/outstanding-reports')
            console.log('response', response.data)
            outstandingReports.value = response.data
        } catch (e) {
            toast.error(e.response.data.message)
        }
    }

    onMounted(() => {
        getOutstandingReports()
    })

    const reportsLabel = computed(() => outstandingReports.value.length === 1 ? 'Report' : 'Reports')

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col md:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
                <div>
                    <JetButton v-if="outstandingReports && outstandingReports.length"
                               type="button"
                               style-type="danger"
                               class="mt-3 md:mt-0 py-3 w-full justify-center md:w-auto font-bold">
                        <Bell color="#fff" class="mr-2"/>
                        {{ outstandingReports.length }} {{ reportsLabel }} Outstanding
                    </JetButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <CartReservation :user="user"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
