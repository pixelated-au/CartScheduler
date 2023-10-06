<script setup>
import DataTable from "@/Components/DataTable.vue";
import UserAdd from "@/Components/Icons/UserAdd.vue";
import JetButton from '@/Jetstream/Button.vue'
import JetDialogModal from '@/Jetstream/DialogModal.vue'
import JetHelpText from "@/Jetstream/HelpText.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'
import JetToggle from '@/Jetstream/Toggle.vue'
import {format, parse} from "date-fns";
// noinspection ES6UnusedImports
import {VTooltip} from 'floating-vue'
import {computed, ref, watch, watchEffect} from "vue";

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

const assignVolunteer = (volunteerId, volunteerName) => {
    emit('assignVolunteer', {volunteerId, volunteerName, location: props.location, shift: props.shift})
    closeModal()
}

const closeModal = () => {
    showModal.value = false
}

const volunteers = ref([])
watchEffect(async () => {
    if (!showModal.value) {
        return
    }
    const response = await axios.get(`/admin/available-users-for-shift/${props.shift.id}`, {
        params: {
            date: format(props.date, 'yyyy-MM-dd'),
            showAll: doShowFilteredVolunteers.value ? 0 : 1,
        }
    })
    volunteers.value = response.data.data
})

const volunteerSearch = ref('')

watch(volunteerSearch, (value) => {

})

const tableHeaders = [
    {
        text: 'ID',
        value: 'id',
        sortable: true,
        width: '10%',
    },
    {
        text: 'Name',
        value: 'name',
        sortable: true,
    },
    {
        text: 'Last Rostered',
        value: 'lastShift',
        sortable: true,
    },
    {
        text: '',
        value: 'action',
        sortable: false,
    },
]

const tableRows = computed(() => {
    return volunteers.value.map(volunteer => {
        const prefix = volunteer.gender === 'male' ? 'Bro' : 'Sis'
        return {
            id: volunteer.id,
            name: `${prefix} ${volunteer.name}`,
            gender: volunteer.gender,
            lastShift: volunteer.last_shift_date ? volunteer.last_shift_date : null,
            lastShiftTime: volunteer.last_shift_start_time ? volunteer.last_shift_start_time : null,
            // lastShift: volunteer.last_rostered_at ? format(parse(volunteer.last_rostered_at, 'yyyy-MM-dd', new Date()), 'MMM d, yyyy') : 'Never',
        }
    })
})

const bodyRowClassNameFunction = item =>
    item.gender === 'male'
        ? 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 transition duration-150 hover:ease-in'
        : 'bg-pink-100 hover:bg-pink-200 dark:bg-fuchsia-900/40 dark:hover:bg-fuchsia-900/60 transition duration-150 hover:ease-in';
const bodyItemClassNameFunction = column => {
    if (column === 'action') return '!text-right';
    return '';
};

const formatShiftDate = (shiftDate, shiftTime) => {
    if (!shiftDate) {
        return 'Never'
    }
    if (!shiftTime) {
        return format(parse(shiftDate, 'yyyy-MM-dd', new Date()), 'MMM d, yyyy')
    }
    return format(parse(`${shiftDate} ${shiftTime}`, 'yyyy-MM-dd HH:mm:ss', new Date()), 'MMM d, yyyy, h:mma')
}

const doShowFilteredVolunteers = ref(true)

const toggleLabel = computed(() => doShowFilteredVolunteers.value
    ? 'Showing available'
    : 'Showing all')
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
                <div class="mt-3 flex justify-end">
                    <div class="flex justify-center w-[150px]">
                        <JetToggle v-model="doShowFilteredVolunteers" :label="toggleLabel"/>
                    </div>
                </div>
            </div>

            <div class="volunteers">
                <data-table
                    :headers="tableHeaders"
                    :items="tableRows"
                    :search-value="volunteerSearch"
                    :filter-options="[]"
                    :show-hover="false"
                    :body-row-class-name="bodyRowClassNameFunction"
                    :body-item-class-name="bodyItemClassNameFunction">
                    <template #item-lastShift="{lastShift, lastShiftTime}">
                        {{ formatShiftDate(lastShift, lastShiftTime) }}
                    </template>
                    <template #item-action="{ id, name }">
                        <JetButton style-type="info" @click="assignVolunteer(id, name)">
                            <UserAdd color="#fff"/>
                        </JetButton>
                    </template>
                </data-table>
            </div>

        </template>

        <template #footer>
            <JetSecondaryButton @click="closeModal">
                Close
            </JetSecondaryButton>

            <!--            <JetDangerButton class="ml-3"-->
            <!--                             :class="{ 'opacity-25': form.processing }"-->
            <!--                             :disabled="form.processing"-->
            <!--                             @click="deleteUser">-->
            <!--                Delete Account-->
            <!--            </JetDangerButton>-->
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
