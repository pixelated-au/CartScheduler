<script setup>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';

import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetSectionBorder from '@/Jetstream/SectionBorder.vue';
import ShiftData from '@/Pages/Admin/Locations/Partials/ShiftData.vue';
import {router, useForm} from '@inertiajs/vue3';
import {computed, nextTick, ref, watch} from 'vue';
import LocationData from './LocationData.vue';

const props = defineProps({
    location: Object,
    maxVolunteers: {
        type: Number,
        required: true,
    },
    action: {
        type: String,
        default: 'edit',
    },
});

const emit = defineEmits([
    'cancel',
]);

const form = useForm({
    id: props.location?.data?.id,
    name: props.location?.data?.name,
    description: props.location?.data?.description,
    min_volunteers: props.location?.data?.min_volunteers,
    max_volunteers: props.location?.data?.max_volunteers,
    requires_brother: props.location?.data?.requires_brother,
    latitude: props.location?.data?.latitude,
    longitude: props.location?.data?.longitude,
    is_enabled: props.location?.data?.is_enabled,
    shifts: props.location?.data?.shifts,
    // id: '',
    // name: '',
    // description: '',
    // min_volunteers: '',
    // max_volunteers: '',
    // requires_brother: '',
    // latitude: '',
    // longitude: '',
    // is_enabled: '',
    // shifts: '',
});

// watchEffect(() => {
//     form.id = props.location?.data?.id
//     form.name = props.location?.data?.name
//     form.description = props.location?.data?.description
//     form.min_volunteers = props.location?.data?.min_volunteers
//     form.max_volunteers = props.location?.data?.max_volunteers
//     form.requires_brother = props.location?.data?.requires_brother
//     form.latitude = props.location?.data?.latitude
//     form.longitude = props.location?.data?.longitude
//     form.is_enabled = props.location?.data?.is_enabled
//     form.shifts = props.location?.data?.shifts
// })

watch(() => form.min_volunteers, (value, oldValue) => {
    if (value < 0) {
        nextTick(() => {
            form.min_volunteers = oldValue;
        });
    }
    if (value > form.max_volunteers) {
        form.max_volunteers = value;
    }
});

watch(() => form.max_volunteers, (value, oldValue) => {
    if (value > props.maxVolunteers) {
        nextTick(() => {
            form.max_volunteers = oldValue;
        });
    }
    if (value < form.min_volunteers) {
        form.min_volunteers = value;
    }
});

const updateLocationData = () => {
    form.put(route('admin.locations.update', props.location.data.id),
        {
            preserveScroll: true,
        });
};

const createLocationData = () => {
    form.post(route('admin.locations.store'), {
        preserveScroll: true,
    });
};

const saveAction = () => {
    if (props.action === 'edit') {
        updateLocationData();
    } else {
        createLocationData();
    }
};

const listRouteAction = () => {
    router.visit(route('admin.locations.index'));
};

const showConfirmationModal = ref(false);
const modalDeleteAction = ref(false);
const confirmCancel = () => {
    modalDeleteAction.value = false;
    if (form.isDirty) {
        showConfirmationModal.value = true;
    } else {
        listRouteAction();
    }
};

const onDelete = () => {
    modalDeleteAction.value = true;
    showConfirmationModal.value = true;
};

const doDeleteAction = () => {
    router.delete(route('admin.locations.destroy', props.location.data.id));
};

const performConfirmationAction = () => {
    if (modalDeleteAction.value) {
        doDeleteAction();
    } else {
        listRouteAction();
    }
};

const cancelButtonText = computed(() => form.isDirty ? 'Cancel' : 'Back');
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

</script>

<template>
    <JetFormSection @submitted="updateLocationData">
        <template #title>
            Location
        </template>

        <template #description>
            Update the location information. This can optionally include latitude and longitude coordinates. These can
            be acquired from Google Maps.
        </template>

        <template #form>
            <LocationData v-model="form" :max-volunteers="maxVolunteers"/>

            <JetSectionBorder class="col-span-full"/>

            <ShiftData v-model="form"/>
            <div></div>
        </template>

        <template #actions>
            <div v-if="action === 'edit'" class="grow text-left">
                <PButton outline
                           type="button"
                           style-type="warning"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="onDelete">
                    Delete
                </PButton>
            </div>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Success: Location Saved.
            </JetActionMessage>
            <div v-if="hasErrors" class="font-bold text-red-600">
                Something went wrong with your data. Please see above.
            </div>

            <div>
                <PButton class="mx-3"
                           type="button"
                           style-type="secondary"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="confirmCancel">
                    {{ cancelButtonText }}
                </PButton>
                <PButton :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="saveAction">
                    Save
                </PButton>
            </div>
        </template>
    </JetFormSection>

    <JetConfirmationModal :show="showConfirmationModal">
        <template #title>DANGER!</template>
        <template #content>
            <template v-if="modalDeleteAction">Are you sure you wish to delete this location?</template>
            <template v-else>Are you sure you wish to return? Your changes will be lost!</template>
        </template>
        <template #footer>
            <PButton class="mx-3" style-type="secondary" @click="showConfirmationModal = false">
                Cancel
            </PButton>
            <PButton severity="warning" @click="performConfirmationAction">Ok</PButton>
        </template>
    </JetConfirmationModal>
</template>
