<script setup>
import DataTable from '@/Components/DataTable.vue';
import Filter from '@/Components/Icons/Filter.vue';
import JetHelpText from '@/Jetstream/HelpText.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {computed, ref} from 'vue';
import headers from './Lib/ReportsDataTableHeaders.js';

const props = defineProps({
    reports: Object,
    locations: Object,
});

const userSearch = ref('');

const reportData = computed(() => props.reports.data.map(report => ({
    id: report.id,
    location: report.metadata?.location_name || report.shift?.location?.name || '',
    locationId: report.metadata?.locationId || report.shift?.location?.id || '',
    userExists: !!report.submitted_by?.id,
    submittedByName: report.metadata?.submitted_by_name || report.submitted_by?.name || '',
    submittedByPhone: report.metadata?.submitted_by_phone || report.submitted_by?.mobile_phone || '',
    submittedByEmail: report.metadata?.submitted_by_email || report.submitted_by?.email || '',
    date: report.shift_date,
    time: report.shift?.start_time,
    placements: report.placements_count,
    videos: report.videos_count,
    requests: report.requests_count,
    comments: report.comments,
    tags: report.tags.map(tag => tag.name.en),
    cancelled: report.shift_was_cancelled,
    associates: report.metadata?.associates || [],
})));

const showLocationFilter = ref(false);
const locationFilter = ref(0);

const filter = computed(() => {
    const filters = [];
    if (locationFilter.value) {
        filters.push({field: 'locationId', comparison: '=', criteria: locationFilter.value});
    }
    return filters;
});
</script>

<template>
    <AppLayout title="Reports">
        <template #header>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Reports</h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pt-10 pb-5 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 shadow-xl sm:rounded-lg sm:p-6">
                <JetLabel for="search" value="Search on Reports"/>
                <JetInput id="search" v-model="userSearch" type="text" class="mt-1 block w-full"/>
                <JetHelpText>Search on location, user name, date, etc</JetHelpText>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-10 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 shadow-xl sm:rounded-lg sm:p-6">
                <data-table :headers="headers"
                            :items="reportData"
                            :search-value="userSearch"
                            :filter-options="filter"
                            :show-hover="false">
                    <template #header-location="{text}">
                        <div class="relative flex items-center">
                            <div
                                class="p-2 mr-2 cursor-pointer inline-block hover:bg-gray-200 hover:bg-opacity-50 rounded"
                                @click.stop="showLocationFilter = !showLocationFilter">
                                <Filter/>
                            </div>
                            {{ text }}
                            <div
                                class="p-2 z-50 absolute top-12 w-64 bg-white border border-gray-300 rounded-md shadow-lg"
                                v-if="showLocationFilter">
                                <select class="location-selector w-full rounded-md"
                                        v-model="locationFilter"
                                        name="location">
                                    <option value="" selected>All</option>
                                    <option v-for="location in locations.data" :key="location.id" :value="location.id">
                                        {{ location.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </template>
                    <template #item-cancelled="{ cancelled }">
                        {{ cancelled ? 'Yes' : 'No' }}
                    </template>
                    <template
                        #expand="{comments, tags, submittedByName, submittedByEmail, submittedByPhone, associates}">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg grid grid-cols-3">
                            <ul v-if="tags?.length" class="col-span-3 p-0 m-0 mb-3">
                                <li v-for="(tag, i) in tags"
                                    :key="i"
                                    class="inline-flex px-2 bg-green-200 dark:bg-green-800 mr-1 mb-1 rounded-full dark:text-gray-50">
                                    {{ tag }}
                                </li>
                            </ul>
                            <div class="">
                                <h5>Comments</h5>
                                <div v-if="comments">{{ comments }}</div>
                                <div v-else class="text-gray-500 dark:text-gray-300"><em>None entered</em></div>
                            </div>
                            <div>
                                <h5>Submitted by: {{ submittedByName }}<br></h5>
                                Tel: <a :href="`tel:${submittedByPhone}`">{{ submittedByPhone }}</a><br>
                                Email: <a :href="`mailto:${submittedByEmail}`">{{ submittedByEmail }}</a>
                            </div>
                            <div>
                                <h5>Associates</h5>
                                <div v-if="associates?.length">
                                    <ul class="p-0 m-0">
                                        <li v-for="(associate, i) in associates"
                                            :key="i"
                                            class="inline-flex px-2 bg-green-200 dark:bg-purple-800 mr-1 mb-1 rounded-full dark:text-gray-50">
                                            {{ associate.name }}
                                        </li>
                                    </ul>

                                </div>
                            </div>
                        </div>
                    </template>
                </data-table>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

</style>
