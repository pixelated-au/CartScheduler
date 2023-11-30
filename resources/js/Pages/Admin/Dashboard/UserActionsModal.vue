<script setup>
import QuestionCircle from "@/Components/Icons/QuestionCircle.vue";
import JetDialogModal from '@/Jetstream/DialogModal.vue'
import JetHelpText from "@/Jetstream/HelpText.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'
import JetToggle from '@/Jetstream/Toggle.vue'
import UserTable from "@/Pages/Admin/Dashboard/UserTable.vue";
import {format} from "date-fns";
import {computed, inject, reactive, ref} from "vue";
import {Menu as VMenu} from 'floating-vue'

const props = defineProps({
    show: Boolean,
    date: Date,
    shift: Object,
    location: Object,
})

const emit = defineEmits(['assignVolunteer', 'update:show'])

const showModal = computed({
    get: () => props.show,
    set: value => emit('update:show', value)
})

const closeModal = () => {
    showModal.value = false
}

const mainFilters = reactive({
    doShowFilteredVolunteers: true,
})

const columnFilters = reactive({
    gender: false,
    appointment: false,
    servingAs: false,
    maritalStatus: false,
    birthYear: false,
    responsibleBrother: false,
})

const volunteerSearch = ref('')

const enableUserAvailability = inject('enableUserAvailability', false)

const toggleLabel = computed(() => mainFilters.doShowFilteredVolunteers
    ? 'Showing available'
    : 'Showing all')

const volunteerAssigned = function (volunteer) {
    emit('assignVolunteer', volunteer)
    closeModal()
}
</script>

<template>
    <JetDialogModal :show="showModal" @close="closeModal">
        <template #title>
            Assign a volunteer to {{ location?.name }} on {{ format(props.date, 'MMM d, yyyy') }}
        </template>

        <template #content>
            <div class="max-w-7xl mx-auto pt-10 pb-5">
                <div class="bg-white dark:bg-gray-900 shadow-xl sm:rounded-lg sm:p-6">
                    <JetLabel for="search" value="Search for a volunteer"/>
                    <JetInput id="search" v-model="volunteerSearch" type="text" class="mt-1 block w-full"/>
                    <JetHelpText>Search on name</JetHelpText>
                </div>
                <div v-if="enableUserAvailability" class="mt-3 flex justify-end items-center">
                    <div class="flex flex-wrap justify-center w-[150px]">
                        <JetToggle v-model="mainFilters.doShowFilteredVolunteers">
                            {{ toggleLabel }}
                            <v-menu class="ml-1 inline-block">
                                <span><QuestionCircle/></span>
                                <template #popper>
                                    <div class="max-w-[300px]">
                                        Note: if an expected volunteer is missing, they're likely already rostered.
                                    </div>
                                </template>
                            </v-menu>
                        </JetToggle>
                    </div>
                </div>
            </div>

            <UserTable :shift-id="shift.id" :date="date" :is-visible="showModal" :text-filter="volunteerSearch" :main-filters="mainFilters"
                       :column-filters="columnFilters" @assignVolunteer="volunteerAssigned"/>

        </template>

        <template #footer>
            <JetSecondaryButton @click="closeModal">
                Close
            </JetSecondaryButton>
        </template>
    </JetDialogModal>
</template>
<style lang="scss">
.volunteers .data-table table {
    border-spacing: 0 2px;

    td:first-child {
        @apply rounded-l-lg;
    }

    td:last-child {
        @apply rounded-r-lg;
    }

}
</style>
