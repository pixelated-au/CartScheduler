<script setup>
import Bell from "@/Components/Icons/Bell.vue";
import useToast from "@/Composables/useToast";
import JetButton from '@/Jetstream/Button.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import {router, usePage} from '@inertiajs/vue3';
import axios from "axios";
import {computed, onMounted, ref} from 'vue';
import vAutoScroll from '@/Directives/v-autoscroll.js';

const props = defineProps({
    settings: Object,
});

const processing = ref(false);
const betaCheck = ref(false);
const toast = useToast();
const updateCheck = async () => {
    processing.value = true;
    try {
        const response = await axios.get(route('admin.check-update'), {params: {beta: !!betaCheck.value}});
        if (!response.data) {
            toast.warning('No update available');
            return;
        }

        toast.success('Update available');
    } finally {
        processing.value = false;
    }
    router.visit(route('admin.settings'), {preserveState: false, preserveScroll: true});
};

const updateLog = ref('');
const doSoftwareUpdate = async () => {
    processing.value = true;
    updateLog.value = '';
    try {
        const response = await axios.post(route('admin.do-update'), {}, {
            responseType: 'stream',
            adapter: 'fetch',
        });
        const reader = response.data.getReader();
        const decoder = new TextDecoder();

        while (true) {
            const {done, value} = await reader.read();
            if (done) {
                break;
            }
            updateLog.value += decoder.decode(value);
        }

        if (response.status === 200) {
            toast.success(`Update process completed. Please check the log for details.`);
        } else {
            toast.error(`Update process failed. Please check the log for details.`);
        }
    } catch (error) {
        console.error('Error during update:', error);
        toast.error('An error occurred during the update. Please try again.');
    } finally {
        processing.value = false;
    }
};

const hasUpdate = computed(() => {
    return !!usePage().props.hasUpdate && !updateLog.value;
});

const adminUsers = ref();
onMounted(async () => {
    const response = await axios.get('/admin/admin-users');
    adminUsers.value = response.data.data;
});
</script>

<template>
    <JetFormSection @submitted="doSoftwareUpdate">
        <template #title>
            Software Update
        </template>

        <template #description>
            <div v-if="hasUpdate" class="flex items-center content-center">
                <div class="inline-block px-0.5 py-0.5 bg-red-500 rounded-full text-xs mr-1">
                    <bell color="#fff" box="12"/>
                </div>
                <div class="font-bold">Update Available!</div>
            </div>
            <div v-else>You are running the latest version of the scheduling software.</div>
        </template>

        <template #form>
            <template v-if="hasUpdate">
                <p class="col-span-12 text-gray-600 dark:text-gray-300 bg-yellow-200 dark:bg-yellow-800 px-3">
                    The latest version is: <span class="font-mono font-bold">{{ settings.availableVersion }}</span>
                    You are running version: <span class="font-mono">{{ settings.currentVersion }}</span>.
                </p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">
                    <a :href="`https://github.com/pixelated-au/CartScheduler/releases/tag/${settings.availableVersion}`"
                       target="_blank">Latest version release notes</a>.
                </p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300 font-bold">
                    Note: Updates should be safe to perform. However, sometimes they don't work as expected. It's
                    generally wise to perform the update out of peak usage hours so potential issues can be addressed
                    with minimum impact.
                </p>
            </template>
            <div v-else-if="updateLog" class="col-span-12 text-gray-600 dark:text-gray-300">
                <div class="font-bold">Update Log:</div>
                <pre v-auto-scroll class="mt-2 rounded-md p-2 font-mono text-sm scroll-smooth max-w-full h-96 max-h-96 overflow-x-scroll bg-gray-200 dark:bg-gray-800">{{ updateLog }}</pre>
            </div>
            <template v-else>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">You are running the latest version of the
                    scheduling software (version {{ settings.currentVersion }}).</p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">You can reinstall the current update. Note that
                    this usually isn't required. Use only if directed to by your IT support.</p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300 font-bold">Do not use the Beta update option
                    unless
                    instructed by your IT support.</p>
            </template>
        </template>

        <template #actions>
            <JetButton v-if="hasUpdate" :class="{ 'opacity-25': processing }" :disabled="processing">
                Update Now
            </JetButton>
            <template v-else-if="!updateLog">
                <label class="mr-3 flex items-center text-gray-400 dark:text-gray-600">
                    <JetCheckbox v-model:checked="betaCheck" value="true"/>
                    <span class="ml-1.5">Check for Beta updates</span>
                </label>
                <JetButton outline style-type="danger" :class="{ 'opacity-25': processing }" class="mr-3"
                           :disabled="processing" @click.prevent.stop="updateCheck">
                    Check for update
                </JetButton>
                <JetButton style-type="warning" :class="{ 'opacity-25': processing }" :disabled="processing">
                    Reinstall Current Version
                </JetButton>
            </template>
        </template>
    </JetFormSection>
</template>
