<script setup>
    import useToast from '@/Composables/useToast.js'
    import JetButton from '@/Jetstream/Button.vue'
    import ConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import ReportForm from '@/Pages/Components/Dashboard/ReportForm.vue'
    import { onMounted, provide, ref } from 'vue'

    defineProps({
        show: {
            type: Boolean,
            default: false,
        },
    })

    const emit = defineEmits(['has-outstanding-reports', 'close'])

    const close = () => emit('close')

    const toast = useToast()

    const outstandingReports = ref([])
    const tags = ref([])
    provide('report-tags', tags)

    const getData = async () => {
        try {
            const [reportsResponse, tagsResponse] = await Promise.all([
                axios.get('/outstanding-reports'),
                axios.get('/get-report-tags'),
            ])
            outstandingReports.value = reportsResponse.data
            emit('has-outstanding-reports', outstandingReports.value.length)

            tags.value = tagsResponse.data.data
        } catch (e) {
            toast.error(e.response.data.message)
        }
    }

    onMounted(() => {
        getData()
    })

    const reportSaved = () => getData()</script>

<template>
    <ConfirmationModal :show="show" @close="close">
        <template #title>
            <h3>Outstanding Reports</h3>
        </template>

        <template #content>
            <div class="">
                <div v-if="outstandingReports?.length" class="mt-5">
                    <div v-for="(report, index) in outstandingReports"
                         :key="`${report.shift_date}-${report.start_time}-${report.user_id}`"
                         class="pb-3 mb-20 border-b border-gray-200">
                        <ReportForm :report="report" @saved="reportSaved(index)"/>
                    </div>
                </div>
                <div v-else>
                    <h4 class="my-3 text-green-700 dark:text-green-400">No more reports outstanding 🎉</h4>
                </div>
            </div>
        </template>

        <template #footer>
            <JetButton type="button" style-type="secondary" @click="close">Close</JetButton>
        </template>
    </ConfirmationModal>
</template>
