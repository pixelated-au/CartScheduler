<script setup>
    import JetActionMessage from '@/Jetstream/ActionMessage.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import JetFormSection from '@/Jetstream/FormSection.vue'
    import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
    import ShiftData from '@/Pages/Admin/Locations/Partials/ShiftData.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { useForm } from '@inertiajs/inertia-vue3'
    import { computed, nextTick, ref, watch } from 'vue'
    import LocationData from './LocationData.vue'

    const props = defineProps({
        location: Object,
        action: {
            type: String,
            default: 'edit',
        },
    })

    const emit = defineEmits([
        'cancel',
    ])

    const form = useForm({
        id: props.location.data.id,
        name: props.location.data.name,
        description: props.location.data.description,
        min_volunteers: props.location.data.min_volunteers,
        max_volunteers: props.location.data.max_volunteers,
        requires_brother: props.location.data.requires_brother,
        latitude: props.location.data.latitude,
        longitude: props.location.data.longitude,
        is_enabled: props.location.data.is_enabled,
        shifts: props.location.data.shifts,
    })

    watch(() => form.min_volunteers, (value, oldValue) => {
        if (value < 0) {
            console.log('value', value, 'oldValue', oldValue)
            nextTick(() => {
                form.min_volunteers = oldValue
            })
        }
        if (value > form.max_volunteers) {
            form.max_volunteers = value
        }
    })

    watch(() => form.max_volunteers, (value, oldValue) => {
        if (value > 4) {
            nextTick(() => {
                form.max_volunteers = oldValue
            })
        }
        if (value < form.min_volunteers) {
            form.min_volunteers = value
        }
    })

    const updateLocationData = () => {
        form.put(route('admin.locations.update', props.location.data.id), {
            errorBag: 'updateLocationData',
            preserveScroll: true,
        })
    }

    const createLocationData = () => {
        form.post(route('admin.locations.store'), {
            errorBag: 'updateLocationData',
            preserveScroll: true,
        })
    }

    const saveAction = () => {
        if (props.action === 'edit') {
            updateLocationData()
        } else {
            createLocationData()
        }
    }

    const listRouteAction = () => {
        Inertia.visit(route('admin.locations.index'))
    }

    const showConfirmationModal = ref(false)
    const modalDeleteAction = ref(false)
    const confirmCancel = () => {
        modalDeleteAction.value = false
        if (form.isDirty) {
            showConfirmationModal.value = true
        } else {
            listRouteAction()
        }
    }

    const onDelete = () => {
        modalDeleteAction.value = true
        showConfirmationModal.value = true
    }

    const doDeleteAction = () => {
        Inertia.delete(route('admin.locations.destroy', props.location.data.id))
    }

    const performConfirmationAction = () => {
        if (modalDeleteAction.value) {
            doDeleteAction()
        } else {
            listRouteAction()
        }
    }

    const cancelButtonText = computed(() => form.isDirty ? 'Cancel' : 'Back')
    const hasErrors = computed(() => Object.keys(form.errors).length > 0)

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
            <LocationData v-model="form"/>

            <JetSectionBorder class="col-span-full"/>

            <ShiftData v-model="form" class="col-span-full"/>
            <div></div>
        </template>

        <template #actions>
            <div v-if="action === 'edit'" class="grow text-left">
                <JetButton outline
                           type="button"
                           style-type="warning"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="onDelete">
                    Delete
                </JetButton>
            </div>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Success: Location Saved.
            </JetActionMessage>
            <div v-if="hasErrors" class="font-bold text-red-600">
                Something went wrong with your data. Please see above.
            </div>

            <div>
                <JetButton class="mx-3"
                           type="button"
                           style-type="secondary"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="confirmCancel">
                    {{ cancelButtonText }}
                </JetButton>
                <JetButton :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="saveAction">
                    Save
                </JetButton>
            </div>
        </template>
    </JetFormSection>

    <JetConfirmationModal :show="showConfirmationModal">
        <template #title>Danger!</template>
        <template #content>
            <template v-if="modalDeleteAction">Are you sure you wish to delete this user?</template>
            <template v-else>Are you sure you wish to return? Your changes will be lost!</template>
        </template>
        <template #footer>
            <JetButton class="mx-3" style-type="secondary" @click="showConfirmationModal = false">
                Cancel
            </JetButton>
            <JetButton style-type="warning" @click="performConfirmationAction">Ok</JetButton>
        </template>
    </JetConfirmationModal>
</template>
