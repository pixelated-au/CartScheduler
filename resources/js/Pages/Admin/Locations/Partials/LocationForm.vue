<script setup>
    import TextEditor from '@/Components/TextEditor.vue'
    import VerticalRadioButtons from '@/Components/VerticalRadioButtons.vue'
    import JetActionMessage from '@/Jetstream/ActionMessage.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import JetFormSection from '@/Jetstream/FormSection.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { useForm } from '@inertiajs/inertia-vue3'
    import { computed, nextTick, ref, watch } from 'vue'

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
        id: props.location.id,
        name: props.location.name,
        description: props.location.description,
        min_volunteers: props.location.min_volunteers,
        max_volunteers: props.location.max_volunteers,
        requires_brother: props.location.requires_brother,
        latitude: props.location.latitude,
        longitude: props.location.longitude,
        is_enabled: props.location.is_enabled,
        shifts: props.location.shifts,
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
        form.put(route('admin.locations.update', props.location.id), {
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
        Inertia.delete(route('admin.locations.destroy', props.location.id))
    }

    const performConfirmationAction = () => {
        if (modalDeleteAction.value) {
            doDeleteAction()
        } else {
            listRouteAction()
        }
    }

    const cancelButtonText = computed(() => form.isDirty ? 'Cancel' : 'Back')
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
            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="name" value="Name"/>
                <JetInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name"/>
                <JetInputError :message="form.errors.name" class="mt-2"/>
            </div>

            <!-- Description -->
            <div class="col-span-6 sm:col-span-full">
                <JetLabel for="description" value="Description"/>
                <TextEditor v-model="form.description"/>
                <JetInputError :message="form.errors.description" class="mt-2"/>
            </div>

            <!-- Minimum Volunteers -->
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
                <JetLabel for="min-volunteers" value="Minimum Volunteers at Location"/>
                <JetInput id="min-volunteers"
                          v-model="form.min_volunteers"
                          type="number"
                          inputmode="number"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.min_volunteers" class="mt-2"/>
            </div>
            <!-- Maximum Volunteers -->
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
                <JetLabel for="max-volunteers">
                    Maximum Volunteers at Location <span class="text-sm">(Max 4)</span>
                </JetLabel>
                <JetInput id="max-volunteers"
                          v-model="form.max_volunteers"
                          type="number"
                          inputmode="number"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.max_volunteers" class="mt-2"/>
            </div>

            <!-- Requires Brother -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Requires Brother to be on shifts for this location?
                </div>
                <VerticalRadioButtons name="role" v-model="form.requires_brother" :options="[
                    { label: 'Yes', value: true },
                    { label: 'No', value: false },
                ]"/>
                <JetInputError :message="form.errors.requires_brother" class="mt-2"/>
            </div>

            <!-- Location Status -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Location Status
                </div>
                <VerticalRadioButtons name="is-enabled" v-model="form.is_enabled" :options="[
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ]"/>
                <JetInputError :message="form.errors.is_enabled" class="mt-2"/>
            </div>

            <!-- Minimum Volunteers -->
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
                <JetLabel for="latitude" value="Location Latitude"/>
                <JetInput id="latitude"
                          v-model="form.latitude"
                          type="number"
                          inputmode="decimal"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.latitude" class="mt-2"/>
            </div>
            <!-- Maximum Volunteers -->
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
                <JetLabel for="longitude" value="Location Longitude"/>
                <JetInput id="longitude"
                          v-model="form.longitude"
                          type="number"
                          inputmode="decimal"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.longitude" class="mt-2"/>
            </div>
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
