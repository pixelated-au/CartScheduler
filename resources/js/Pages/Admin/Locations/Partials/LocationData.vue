<script setup>
import HorizontalRadioButtons from '@/Components/HorizontalRadioButtons.vue';
import TextEditor from '@/Components/TextEditor.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import {computed, defineProps} from 'vue';

const props = defineProps({
    modelValue: Object,
    maxVolunteers: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits([
    'update:modelValue',
]);

const form = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    },
});
</script>

<template>
    <!-- Name -->
    <div class="col-span-6 sm:col-span-4">
        <JetLabel for="name" value="Name"/>
        <JetInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name"/>
        <JetInputError :message="form.errors.name" class="mt-2"/>
    </div>

    <!-- Description -->
    <div class="col-span-6 sm:col-span-full">
        <JetLabel for="description" value="Description"/>
        <p class="mb-1 text-sm italic text-gray-500 dark:text-gray-500">
            NOTE: As part of standard HTML, empty paragraphs won't appear. A future update will have the ability to
            create arbitrary spacing
        </p>
        <p class="mb-1 text-sm text-gray-700 dark:text-gray-300">
            Return/Enter creates a new paragraph. Holding the Shift key while pressing Return/Enter will insert a line
            break.
        </p>
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
            Maximum Volunteers at Location <span class="text-sm">(Max {{ maxVolunteers }})</span>
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
        <div class="font-medium text-gray-700 dark:text-gray-100">
            Requires Brother to be on shifts for this location?
        </div>
        <HorizontalRadioButtons name="role" v-model="form.requires_brother" :options="[
                    { label: 'Yes', value: true },
                    { label: 'No', value: false },
                ]"/>
        <JetInputError :message="form.errors.requires_brother" class="mt-2"/>
    </div>

    <!-- Location Status -->
    <div class="col-span-6 sm:col-span-4">
        <div class="font-medium text-gray-700 dark:text-gray-100">
            Location Status
        </div>
        <HorizontalRadioButtons name="is-enabled" v-model="form.is_enabled" :options="[
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ]"/>
        <JetInputError :message="form.errors.is_enabled" class="mt-2"/>
    </div>

    <!--    <div class="col-span-6 sm:col-span-4 md:col-span-3">-->
    <!--        <JetLabel for="latitude" value="Location Latitude"/>-->
    <!--        <JetInput id="latitude" v-model="form.latitude" type="number" inputmode="decimal" class="mt-1 block w-full"/>-->
    <!--        <div class="text-xs text-yellow-500">Not in use</div>-->
    <!--        <JetInputError :message="form.errors.latitude" class="mt-2"/>-->
    <!--    </div>-->
    <!--    <div class="col-span-6 sm:col-span-4 md:col-span-3">-->
    <!--        <JetLabel for="longitude" value="Location Longitude"/>-->
    <!--        <JetInput id="longitude" v-model="form.longitude" type="number" inputmode="decimal" class="mt-1 block w-full"/>-->
    <!--        <div class="text-xs text-yellow-500">Not in use</div>-->
    <!--        <JetInputError :message="form.errors.longitude" class="mt-2"/>-->
    <!--    </div>-->

</template>
