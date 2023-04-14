<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import JetDialogModal from '@/Jetstream/DialogModal.vue'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'
    import {computed, inject, ref, watchEffect} from "vue";
    import UserAdd from "@/Components/Icons/UserAdd.vue";
    import {format} from "date-fns";
    import DataTable from "@/Components/DataTable.vue";
    import JetLabel from "@/Jetstream/Label.vue";
    import JetInput from "@/Jetstream/Input.vue";
    import JetHelpText from "@/Jetstream/HelpText.vue";

    const props = defineProps({
        show: Boolean,
        date: Date,
        shift: Object,
        location: Object,
    })

    const emit = defineEmits(['assignVolunteer', 'update:show'])

    const isDarkMode = inject('darkMode', false)

    const showModal = computed({
        get: () => props.show,
        set: value => emit('update:show', value)
    })

    const assignVolunteer = volunteerId => {
        emit('assignVolunteer', volunteerId)
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
                date: format(props.date, 'yyyy-MM-dd')
            }
        })
        volunteers.value = response.data.data
    })

    const volunteerSearch = ref('')

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
            text: '',
            value: 'action',
            sortable: false,
        }
        // {
        //     text: 'Last Rostered',
        //     value: 'date',
        //     sortable: true,
        // },
    ]

    // const tableRows = computed(() => {
    //     return volunteers.value.map(volunteer => {
    //         return {
    //             id: volunteer.id,
    //             name: volunteer.name,
    //             // date: volunteer.last_rostered_at ? format(parse(volunteer.last_rostered_at, 'yyyy-MM-dd', new Date()), 'MMM d, yyyy') : 'Never',
    //         }
    //     })
    // })

    const bodyItemClassNameFunction = column => {
        if (column === 'action') return 'text-right';
        return '';
    };

</script>

<template>
    <JetDialogModal :show="showModal" @close="closeModal">
        <template #title>
            Assign a volunteer to {{ location?.name }} on {{ format(props.date, 'MMM d, yyyy') }}
        </template>

        <template #content>
            <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 shadow-xl sm:rounded-lg sm:p-6">
                    <JetLabel for="search" value="Search for a volunteer"/>
                    <JetInput id="search" v-model="volunteerSearch" type="text" class="mt-1 block w-full"/>
                    <JetHelpText>Search on name</JetHelpText>
                </div>
            </div>

            <data-table :headers="tableHeaders"
                        :items="volunteers"
                        :search-value="volunteerSearch"
                        :filter-options="[]"
                        :show-hover="false"
                        :body-item-class-name="bodyItemClassNameFunction">
                <!--                <template #header-id="{text}">-->
                <!--                    {{ text }}-->
                <!--                </template>-->
                <!--                <template #name="{ text }">-->
                <!--                    {{ text }}-->
                <!--                </template>-->
                <template #item-action="{ item }">
                    <JetButton style-type="info" @click="assignVolunteer">
                        <UserAdd :color="isDarkMode ? '#fff' : '#000'"/>
                    </JetButton>
                </template>
            </data-table>


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
</style>
