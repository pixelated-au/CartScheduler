<script setup>
import useToast from "@/Composables/useToast";
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import {Inertia} from '@inertiajs/inertia';
import {usePage} from '@inertiajs/inertia-vue3';
import axios from "axios";
import {computed, onMounted, ref} from 'vue';

const props = defineProps({
    settings: Object,
})

const processing = ref(false)
const toast = useToast()
const updateCheck = async () => {
    processing.value = true
    try {
        const response = await axios.get(route('admin.check-update'))
        if (!response.data) {
            toast.warning('No update available')
            return
        }

        toast.success('Update available')
    } finally {
        processing.value = false
    }
    Inertia.visit(route('admin.settings'), {preserveState: false, preserveScroll: true})
}

const updateLog = ref('')
const doSoftwareUpdate = async () => {
    processing.value = true
    try {
        const response = await axios.post(route('admin.do-update'))
        console.log('doSoftwareUpdate', response)
        toast.success(`Update successful! You are now running version: ${props.settings.currentVersion}`)
        updateLog.value = response.data
    } finally {
        processing.value = false
    }

}

const hasUpdate = computed(() => {
    return !!usePage().props.value.hasUpdate && !updateLog.value
})

const adminUsers = ref()
onMounted(async () => {
    const response = await axios.get('/admin/admin-users')
    adminUsers.value = response.data.data
})

</script>

<template>
    <JetFormSection @submitted="doSoftwareUpdate">
        <template #title>
            Software Update
        </template>

        <template #description>
            <template v-if="hasUpdate"><span class="font-bold">Update Available!</span></template>
            <template v-else>You are running the latest version of the scheduling software.</template>
        </template>

        <template #form>
            <template v-if="hasUpdate">
                <p class="col-span-12 text-gray-600 dark:text-gray-300">
                    The latest version is: <span class="font-mono font-bold">{{ settings.availableVersion }}</span>
                    You are running version: <span class="font-mono">{{ settings.currentVersion }}</span>.
                </p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">
                    <a :href="`https://github.com/pixelated-au/CartScheduler/releases/tag/${settings.availableVersion}`"
                       target="_blank">Latest version release notes</a>.
                </p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300 font-bold">
                    Note: Updates should be safe. However, things can always go wrong. It's generally wise to perform
                    the update out of peak usage hours so potential issues can be addressed with minimum impact.
                </p>
            </template>
            <div v-else-if="updateLog" class="col-span-12 text-gray-600 dark:text-gray-300">
                <div class="font-bold">Update Log:</div>
                <pre class="font-mono">{{updateLog}}</pre>
            </div>
            <template v-else>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">You are running the latest version of the
                    scheduling software (version {{ settings.currentVersion }}).</p>
                <p class="col-span-12 text-gray-600 dark:text-gray-300">You can reinstall the current update. Note that
                    this usually isn't required. Use only if directed to by your IT support.</p>

            </template>
        </template>

        <template #actions>
            <JetButton v-if="hasUpdate" :class="{ 'opacity-25': processing }" :disabled="processing">
                Update Now
            </JetButton>
            <template v-else-if="!updateLog">
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
