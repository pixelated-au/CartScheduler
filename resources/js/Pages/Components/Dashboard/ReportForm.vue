<script setup>
import useToast from '@/Composables/useToast.js';

import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import ReportTags from '@/Pages/Components/Dashboard/ReportTags.vue';
import axios from 'axios';
import {format, parse} from 'date-fns';
import {computed, nextTick, reactive, ref, watch} from 'vue';

const props = defineProps({
    report: Object,
});

const emit = defineEmits(['saved']);

const formData = reactive({
    shift_id: props.report.shift_id,
    shift_date: props.report.shift_date,
    start_time: props.report.start_time,
    shift_was_cancelled: props.report.shift_was_cancelled,
    placements_count: props.location?.data?.placements_count,
    videos_count: props.location?.data?.videos_count,
    requests_count: props.location?.data?.requests_count,
    comments: props.location?.data?.comments,
    tags: [],
});

const errors = ref({});
const errorMessages = computed(() => {
    const messages = {};
    for (const key in errors.value) {
        if (errors.value.hasOwnProperty(key)) {
            messages[key] = errors.value[key].join(', ');
        }
    }
    return messages;
});

const toast = useToast();

const isSaving = ref(false);
const saveReport = async () => {
    if (isSaving.value) {
        return;
    }
    try {
        isSaving.value = true;
        errors.value = {};
        const response = await axios.post(route('save.report'), formData);
        toast.success(response.data.message);
        emit('saved');
    } catch (e) {
        errors.value = e.response.data.errors;
        toast.error(e.response.data.message);
    } finally {
        isSaving.value = false;
    }
};

const formatDate = (date) => {
    return format(new Date(date), 'E, do MMMM yyyy');
};

const fieldUnique = computed(() => Math.random().toString(36).substring(2, 9));

const maxCommentChars = 500;
const commentsRemainingCharacters = computed(() => {
    if (formData.comments) {
        return maxCommentChars - formData.comments.length;
    }
    return maxCommentChars;
});

watch(() => formData.comments, (value, oldValue) => {
    if (value.length > maxCommentChars) {
        nextTick(() => {
            formData.comments = oldValue;
        });
    }
});

const disableFields = computed(() => !!formData.shift_was_cancelled);

const formatTime = time => format(parse(time, 'HH:mm:ss', new Date()), 'h:mm a');
</script>

<template>
    <h4><span class="mr-1">{{ formatDate(report.shift_date) }}:</span> {{ report.location_name }}</h4>
    <div class="mb-3">{{ formatTime(report.start_time) }} - {{ formatTime(report.end_time) }}</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6">
        <div class="sm:col-span-2 flex items-center">
            <JetCheckbox :id="`${fieldUnique}-cancelled`"
                         v-model:checked="formData.shift_was_cancelled"
                         value="true"
                         class="mr-3"/>
            <JetLabel :for="`${fieldUnique}-cancelled`" value="Shift was Canceled"/>
        </div>

        <div class="sm:col-span-2 mb-4 text-left">
            <div class="block font-medium mb-2">Quick Tags</div>
            <ReportTags @toggled="formData.tags = $event"/>
            <div class="text-sm text-gray-500">Select any relevant tags</div>
        </div>

        <div class="mb-3 text-left text-left">
            <JetLabel :for="`${fieldUnique}-placements`" value="Placements" :is-disabled="disableFields"/>
            <JetInput :id="`${fieldUnique}-placements`"
                      v-model="formData.placements_count"
                      type="number"
                      class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      :disabled="disableFields"/>
            <JetInputError :message="errorMessages.placements_count" class="mt-2"/>
        </div>
        <div class="mb-3 text-left">
            <JetLabel :for="`${fieldUnique}-videos`" value="Videos" :is-disabled="disableFields"/>
            <JetInput :id="`${fieldUnique}-videos`"
                      v-model="formData.videos_count"
                      type="number"
                      class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      :disabled="disableFields"/>
            <JetInputError :message="errorMessages.videos_count" class="mt-2"/>
        </div>
        <div class="mb-3 text-left">
            <JetLabel :for="`${fieldUnique}-requests`" value="Requests" :is-disabled="disableFields"/>
            <JetInput :id="`${fieldUnique}-requests`"
                      v-model="formData.requests_count"
                      type="number"
                      class="mt-1 block w-full disabled:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      :disabled="disableFields"/>
            <JetInputError :message="errorMessages.requests_count" class="mt-2"/>
        </div>
        <div class="sm:col-span-2 mb-3 text-left">
            <JetLabel :for="`${fieldUnique}-comments`" value="Comments"/>
            <textarea :id="`${fieldUnique}-comments`"
                      class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm w-full h-40 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                      v-model="formData.comments"/>
            <div class="text-sm">{{ commentsRemainingCharacters }} characters remaining</div>
            <JetInputError :message="errorMessages.comments" class="mt-2"/>
        </div>

        <div class="mb-3">
            <PButton type="button" style-type="primary" @click.prevent="saveReport" :disabled="isSaving">
                Save Report
            </PButton>
        </div>
    </div>

</template>
