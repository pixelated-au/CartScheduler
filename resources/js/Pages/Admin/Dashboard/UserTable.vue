<script setup>
import useToast from "@/Composables/useToast";
import JetButton from "@/Jetstream/Button.vue";
import DataTable from "@/Components/DataTable.vue";
import Comment from "@/Components/Icons/Comment.vue";
import QuestionCircle from "@/Components/Icons/QuestionCircle.vue";
import UserAdd from "@/Components/Icons/UserAdd.vue";
import FilledShiftsIndicator from "@/Pages/Admin/Dashboard/FilledShiftsIndicator.vue";
import {format, parse} from "date-fns";
import {computed, inject, ref, watchEffect} from "vue";
// noinspection ES6UnusedImports
import {Menu as VMenu, VTooltip} from 'floating-vue'

const props = defineProps({
    shiftId: {
        type: Number,
        required: true,
    },
    date: {
        type: Date,
        required: true,
    },
    isVisible: {
        type: Boolean,
        required: false,
        default: false,
    },
    textFilter: {
        type: String,
        required: false,
        default: '',
    },
    mainFilters: {
        type: Object,
        required: true,
    },
    columnFilters: {
        type: Object,
        required: true,
    },
})

defineEmits(['assignVolunteer'])

const enableUserAvailability = inject('enableUserAvailability', false)
const volunteers = ref([])

const tableHeaders = computed(() => {
    const headers = [
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
    ]
    if (enableUserAvailability) {
        headers.push({
            text: 'Availability',
            value: 'filledShifts',
            sortable: true,
        })
    }
    headers.push({
        text: '',
        value: 'action',
        sortable: false,
    })
    return headers
})

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
            comment: volunteer.availability_comments,
            lastShift: volunteer.last_shift_date ? volunteer.last_shift_date : null,
            lastShiftTime: volunteer.last_shift_start_time ? volunteer.last_shift_start_time : null,
            filledShifts: calcShiftPercentage(daysAlreadyRostered, daysAvailable),
            daysAlreadyRostered,
            daysAvailable,
        }
    })
})

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

const assignVolunteer = (volunteerId, volunteerName) => {
    emit('assignVolunteer', {volunteerId, volunteerName, location: props.location, shift: props.shift})
}

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

const toast = useToast()

watchEffect(async () => {
    if (!props.isVisible) {
        return
    }
    try {
        const response = await axios.get(`/admin/available-users-for-shift/${props.shiftId}`, {
            params: {
                date: format(props.date, 'yyyy-MM-dd'),
                showAll: props.mainFilters.doShowFilteredVolunteers ? 0 : 1,
            }
        })
        volunteers.value = response.data.data
    } catch (e) {
        console.log(e)
        toast.error('Unable to load volunteers, a critical error has occurred.')
    }
})


</script>
<template>
    <div class="volunteers">
        <data-table
                :headers="tableHeaders"
                :items="tableRows"
                :search-value="textFilter"
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
                                <small
                                        class="self-center text-center text-xs border-slate-500 border-r pr-1 mr-2 w-8">
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
                            <p class="text-sm mb-3">In this example, the above volunteer has been rostered on
                                62% of the shifts he's made himself available for. Each month, he's made himself
                                available for 3 Tuesdays, 1 Friday and 4 Saturdays, but <em>this month</em>
                                (i.e. the month selected in the calendar) he's only been rostered on for 2
                                Tuesdays and 3 Saturdays.</p>
                            <p class="text-sm">Note, the percentage figure does not take into account extra
                                shifts the volunteer has taken outside of his 'regular' availability. In the
                                example above, if the volunteer accepted an extra shift on a Sunday, this wont
                                affect the percentage.</p>
                        </div>
                    </template>
                </v-menu>
                {{ header.text }}
            </template>
            <template #item-name="{name, comment}">
                {{ name }}
                <template v-if="comment">
                    <v-menu class="mr-2 inline-block">
                        <span><Comment/></span>
                        <template #popper>
                            <div>
                                <h6>{{ name }} comments:</h6>
                                <div>{{ comment }}</div>
                            </div>
                        </template>
                    </v-menu>
                </template>
            </template>
            <template #item-lastShift="{lastShift, lastShiftTime}">
                {{ formatShiftDate(lastShift, lastShiftTime) }}
            </template>
            <template v-if="enableUserAvailability"
                      #item-filledShifts="{daysAlreadyRostered, daysAvailable, filledShifts}">
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