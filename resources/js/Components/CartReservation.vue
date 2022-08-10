<script setup>
    import Accordion from '@/Components/Accordion.vue'
    import useLocationFilter from '@/Pages/Admin/Locations/Composables/useLocationFilter'
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'

    const { date, locations } = useLocationFilter()
</script>

<template>
    <div class="flex">
        <div class="p-6 sm:px-3 bg-white border-b border-gray-200">
            <Datepicker inline auto-apply :enable-time-picker="false" v-model="date">
                <template #day="{day, date}">
                    <pre class="text-sm">{{ day }}</pre>
                </template>
            </Datepicker>
            <!--            <pre class="text-sm">{{ date.getDay() }} - {{ date }}</pre>-->
        </div>

        <div class="flex-1 sm:p-3 text-sm">
            <Accordion :items="locations" label="name" uid="id">
                <template v-slot="{location}">
                    <ul class="pl-6">
                        <li v-for="shift in location.filterShifts" class="list-disc mb-2">
                            <div>{{ shift.start_time }} - {{ shift.end_time }}</div>
                            <ul class="pl-6">
                                <li v-for="volunteer in shift.filterVolunteers" class="list-disc">
                                    <div>{{ volunteer.name }}</div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </template>
            </Accordion>
            <!--            <ul>-->
            <!--                <li v-for="location in locations" class="mb-6">-->
            <!--                    <div class="font-bold">{{ location.name }}</div>-->
            <!--                    <ul class="pl-6">-->
            <!--                        <li v-for="shift in location.filterShifts" class="list-disc mb-2">-->
            <!--                            <div>{{ shift.start_time }} - {{ shift.end_time }}</div>-->
            <!--                            <ul class="pl-6">-->
            <!--                                <li v-for="volunteer in shift.filterVolunteers" class="list-disc">-->
            <!--                                    <div>{{ volunteer.name }}</div>-->
            <!--                                </li>-->
            <!--                            </ul>-->
            <!--                        </li>-->
            <!--                    </ul>-->
            <!--                </li>-->
            <!--            </ul>-->
        </div>
    </div>
</template>
