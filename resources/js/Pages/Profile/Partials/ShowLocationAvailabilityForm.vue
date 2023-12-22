<script setup>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from "@/Jetstream/Label.vue";
import {useForm} from '@inertiajs/inertia-vue3';
// noinspection ES6UnusedImports
import {Dropdown as VDropdown, VTooltip} from 'floating-vue'
import {computed, onMounted, ref} from "vue";

const props = defineProps({
    selectedLocations: {
        type: Array,
        required: true,
    },
    userId: {
        type: Number,
        default: null,
    },
})
const form = useForm({
    selectedLocations: props.selectedLocations || [],
})

const update = () => {
    form.transform((data) => {
            if (props.userId) {
                return {
                    ...data,
                    user_id: props.userId,
                }
            }
            return data
        })
        .put(route('update.user.location-choices'), {
            preserveScroll: true,
            onSuccess: () => {
                form.defaults({
                    selectedLocations: props.selectedLocations || [],
                })
                form.reset()
            },
            onError: (r) => {
                console.log('error', r)
            },
        });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
}

const onChecked = (locationId, isChecked) => {
    if (!isChecked) {
        form.selectedLocations = form.selectedLocations.filter((id) => id !== locationId)
    } else {
        form.selectedLocations.push(locationId)
    }
}

const errors = computed(() => Object.keys(form.errors)
    .map((key) => {
        const segments = key.split('.');
        if (!segments.length > 1 || segments[0] !== 'selectedLocations') {
            return null;
        }

        const locationIdx = parseInt(segments[segments.length - 1]);
        return {
            locationId: form.selectedLocations[locationIdx],
            message: form.errors[key],
        };
    })
    .filter((error) => error !== null)
)

const getError = (locationId) => {
    const error = errors.value.find((error) => error.locationId === locationId)
    if (!error) {
        return null
    }
    return error.message
}

const locations = ref()
onMounted(async () => {
    const response = await axios.get(route('user.location-choices'))
    locations.value = response.data.data
})
</script>

<template>
    <JetFormSection @submitted="update">
        <template #title>
            Locations
        </template>

        <template #description>
            Indicate which locations
            {{ props.userId ? 'this volunteer' : 'you' }}
            are convenient for you to volunteer at.
        </template>

        <template #form>
            <div class="col-span-6 grid grid-cols-6 gap-3">
                <div v-for="location in locations" :key="location.id" class="col-span-2 flex items-center flex-wrap">
                    <JetCheckbox :id="`do_enable_location_choice_${location.id}`"
                                 :checked="form.selectedLocations.includes(location.id)"
                                 value="true"
                                 class="mr-3"
                                 @update:checked="onChecked(location.id, $event)"/>
                    <JetLabel :for="`do_enable_location_choice_${location.id}`" :value="location.name"
                              class="select-none"/>
                    <JetInputError :message="getError(location.id)" class="mt-2"/>
                </div>
            </div>
            <JetInputError :message="form.errors.featureDisabled" class="col-span-6 mt-2"/>
        </template>

        <template #actions>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </JetActionMessage>
            <div v-if="form.hasErrors" class="text-red-500 font-bold mr-3">
                An error occurred with the data. See above.
            </div>
            <JetButton style-type="secondary" type="button" class="mr-3" @click="resetForm">
                Cancel
            </JetButton>
            <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </JetButton>
        </template>
    </JetFormSection>
</template>
