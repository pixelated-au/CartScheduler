import { isAfter, isBefore } from 'date-fns'
import formatISO from 'date-fns/formatISO'
import { cloneDeep } from 'lodash'
import { computed, onMounted, ref } from 'vue'

export default function useLocationFilter (canViewHistorical = false) {
    /**
     * @param {Date} date
     */
    const date = ref(new Date())

    const serverLocations = ref([])
    const serverDates = ref([])

    const getShifts = async () => {
        const extra = canViewHistorical ? '/1' : ''
        const response = await axios.get(`/shifts${extra}`)
        serverLocations.value = response.data.locations
        serverDates.value = response.data.shifts
    }

    onMounted(() => {
        getShifts()
    })

    const selectedDate = computed({
        get () {
            return date.value ? formatISO(date.value, { representation: 'date' }) : ''
        },
        set (value) {
            date.value = value
        },
    })

    const setReservations = (maxVolunteers, shift) => {
        const volunteers = shift.filterVolunteers?.sort((a, b) => {
            if (a.gender > b.gender) {
                return -1
            }
            if (a.gender < b.gender) {
                return 1
            }
            if (a.name < b.name) {
                return -1
            }
            if (a.name > b.name) {
                return 1
            }
            return 0
        })
        const length = maxVolunteers >= volunteers.length ? maxVolunteers - volunteers.length : maxVolunteers
        if (length) {
            const nullArray = Array(length).fill(null)
            shift.filterVolunteers = [...volunteers, ...nullArray]
        }
    }

    const addShift = (shifts, shift) => {
        if (shift.available_from) {
            const from = new Date(shift.available_from)
            if (isBefore(date.value, from)) {
                return
            }
        }
        if (shift.available_to) {
            const to = new Date(shift.available_to)
            if (isAfter(date.value, to)) {
                return
            }
        }
        shifts.push(shift)
    }

    const addLocation = (mappedLocations, location, shift) => {
        const alreadyAddedLocation = mappedLocations.find(l => l.id === location.id)
        if (!alreadyAddedLocation) {
            location.filterShifts = []
            addShift(location.filterShifts, shift)
            mappedLocations.push(location)
        } else {
            if (!alreadyAddedLocation.filterShifts.find(s => s.id === shift.id)) {
                addShift(alreadyAddedLocation.filterShifts, shift)
            }
        }

    }

    const locations = computed(() => {
        if (!serverLocations?.value) {
            return
        }
        const mappedLocations = []
        const myLocations = cloneDeep(serverLocations.value)
        for (const location of myLocations) {
            for (const shift of location.shifts) {
                const volunteers = shift.volunteers
                shift.filterVolunteers = volunteers.filter(volunteer => volunteer.shift_date === selectedDate.value)
                delete shift.volunteers
                if (location.requires_brother) {
                    let femaleCount = 0
                    for (const filVolunteer of shift.filterVolunteers) {
                        if (filVolunteer.gender === 'female') {
                            femaleCount++
                        }
                    }
                    shift.maxedFemales = femaleCount >= location.max_volunteers - 1
                }
                setReservations(location.max_volunteers, shift)
                const dayOfWeek = date.value.getDay()
                const mappedDay = shift.js_days[dayOfWeek]
                if (mappedDay === true) {
                    addLocation(mappedLocations, location, shift)
                }
            }
            delete location.shifts
        }
        return mappedLocations
    })

    return {
        date,
        locations,
        serverDates,
        getShifts,
    }
}
