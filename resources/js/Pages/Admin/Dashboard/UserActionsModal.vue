<script setup>
import DataTable from "@/Components/DataTable.vue";
import QuestionCircle from "@/Components/Icons/QuestionCircle.vue";
import UserAdd from "@/Components/Icons/UserAdd.vue";
import useToast from "@/Composables/useToast";
import JetButton from '@/Jetstream/Button.vue'
import JetDialogModal from '@/Jetstream/DialogModal.vue'
import JetHelpText from "@/Jetstream/HelpText.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'
import JetToggle from '@/Jetstream/Toggle.vue'
import FilledShiftsIndicator from "@/Pages/Admin/Dashboard/FilledShiftsIndicator.vue";
import {format, parse} from "date-fns";
// noinspection ES6UnusedImports
import {Menu as VMenu, VTooltip} from 'floating-vue'
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
const toast = useToast()
watchEffect(async () => {
  if (!showModal.value) {
    return
  }
  try {
    const response = await axios.get(`/admin/available-users-for-shift/${props.shift.id}`, {
      params: {
        date: format(props.date, 'yyyy-MM-dd'),
        showAll: doShowFilteredVolunteers.value ? 0 : 1,
      }
    })
    volunteers.value = response.data.data
  } catch (e) {
    toast.error('Unable to load volunteers, a critical error has occurred.')
  }
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
    text: 'Shifts',
    value: 'filledShifts',
    sortable: true,
  },
  {
    text: '',
    value: 'action',
    sortable: false,
  },
]

const calcShiftPercentage = (daysRostered, daysAvailable) => {
  if (!daysAvailable) {
    return 0
  }
  let sumOfDaysRostered = 0
  let sumOfDaysAvailable = 0
  for (const day in daysAvailable) {
    if (!daysAvailable.hasOwnProperty(day) || !daysAvailable[day]) {
      continue
    }
    // Not using Array.reduce because we're only calculating based on the days a volunteer is available
    sumOfDaysRostered += daysRostered[day]
    sumOfDaysAvailable += daysAvailable[day]
    if (sumOfDaysRostered > sumOfDaysAvailable) {
      sumOfDaysRostered = sumOfDaysAvailable
    }
  }
  return Math.round((sumOfDaysRostered / sumOfDaysAvailable) * 100)
}

const tableRows = computed(() => {
  return volunteers.value.map(volunteer => {
    const prefix = volunteer.gender === 'male' ? 'Bro' : 'Sis'
    const daysAvailable = {
      sunday: volunteer.num_sundays,
      monday: volunteer.num_mondays,
      tuesday: volunteer.num_tuesdays,
      wednesday: volunteer.num_wednesdays,
      thursday: volunteer.num_thursdays,
      friday: volunteer.num_fridays,
      saturday: volunteer.num_saturdays,
    }
    const daysAlreadyRostered = {
      sunday: (volunteer.filled_sundays < daysAvailable.sunday ? volunteer.filled_sundays : daysAvailable.sunday) || 0,
      monday: (volunteer.filled_mondays < daysAvailable.monday ? volunteer.filled_mondays : daysAvailable.monday) || 0,
      tuesday: (volunteer.filled_tuesdays < daysAvailable.tuesday ? volunteer.filled_tuesdays : daysAvailable.tuesday) || 0,
      wednesday: (volunteer.filled_wednesdays < daysAvailable.wednesday ? volunteer.filled_wednesdays : daysAvailable.wednesday) || 0,
      thursday: (volunteer.filled_thursdays < daysAvailable.thursday ? volunteer.filled_thursdays : daysAvailable.thursday) || 0,
      friday: (volunteer.filled_fridays < daysAvailable.friday ? volunteer.filled_fridays : daysAvailable.friday) || 0,
      saturday: (volunteer.filled_saturdays < daysAvailable.saturday ? volunteer.filled_saturdays : daysAvailable.saturday) || 0,
    }

    return {
      id: volunteer.id,
      name: `${prefix} ${volunteer.name}`,
      gender: volunteer.gender,
      lastShift: volunteer.last_shift_date ? volunteer.last_shift_date : null,
      lastShiftTime: volunteer.last_shift_start_time ? volunteer.last_shift_start_time : null,
      filledShifts: calcShiftPercentage(daysAlreadyRostered, daysAvailable),
      daysAlreadyRostered,
      daysAvailable,
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
                <div v-if="$page.props.enableUserAvailability" class="mt-3 flex justify-end">
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
                    <template #header-filledShifts="header">
                        <v-menu class="mr-2 inline-block">
                            <span><QuestionCircle/></span>
                            <template #popper>
                                <div class="max-w-[300px]">
                                    <p class="text-sm font-bold">Diagram Explanation</p>
                                    <div class="flex gap-x-1 mt-2 mb-3">
                                        <small class="self-center text-center text-xs border-slate-500 border-r pr-1 mr-2 w-8">
                                            %<br>62
                                        </small>
                                        <template
                                                v-for="(days, key) in {tu: {a: 3, f: 2}, fr: {a: 1, f: 0}, sa:{a: 4, f: 3}}"
                                                :key="key">
                                            <small v-if="days" class="block text-center">
                                                <span>{{ key }}</span><br>
                                                <FilledShiftsIndicator :available="days.a" :filled="days.f"/>
                                            </small>
                                        </template>
                                    </div>
                                    <p class="text-sm mb-3">The above volunteer has made themselves available for 62% of
                                        shifts in total. <span class="italic">Each month</span>, they've made themselves
                                        available for 3 Tuesdays, 1 Friday and 4 Saturdays, but have only been rostered
                                        on for 2 Tuesdays and 3 Saturdays.</p>
                                    <p class="text-sm">Note, the percentage figure does not take into account extra
                                        shifts the volunteer has taken outside of their 'regular' availability. In the
                                        example above, if the volunteer accepted a shift on a Sunday, this wont affect
                                        the percentage.</p>
                                </div>
                            </template>
                        </v-menu>
                        {{ header.text }}
                    </template>
                    <template #item-lastShift="{lastShift, lastShiftTime}">
                        {{ formatShiftDate(lastShift, lastShiftTime) }}
                    </template>
                    <template #item-filledShifts="{daysAlreadyRostered, daysAvailable, filledShifts}">
                        <div class="flex gap-x-1">
                            <small class="self-center text-center text-xs border-slate-500 border-r pr-1 mr-2 w-8">
                                %<br>{{ filledShifts }}
                            </small>
                            <template v-for="(days, key) in daysAvailable" :key="key">
                                <small v-if="days" class="block text-center">
                                    <span>{{ key.substring(0, 2) }}</span><br>
                                    <FilledShiftsIndicator :available="days" :filled="daysAlreadyRostered[key]"/>
                                </small>
                            </template>
                        </div>
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
