<script setup>
import SelectField from "@/Components/SelectField.vue";
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import {useForm} from '@inertiajs/inertia-vue3';
import {computed} from "vue";

const props = defineProps({
  settings: Object,
});

const form = useForm({
  _method: 'PUT',
  siteName: props.settings.siteName,
  systemShiftStartHour: props.settings.systemShiftStartHour,
  systemShiftEndHour: props.settings.systemShiftEndHour,
});

const updateGeneralSettings = () => {
  form.put(route('admin.general-settings.update'), {
    errorBag: 'updateGeneralSettings',
    preserveScroll: true,
    onSuccess: () => form.defaults(),
  })
}

const hours = computed(() => {
  const hours = [];
  for (let i = 1; i < 24; i++) {
    hours.push({label: i + (i < 12 ? 'am' : 'pm'), value: i});
  }
  hours.push({label: '12am', value: 0})
  return hours;
});

</script>

<template>
    <JetFormSection @submitted="updateGeneralSettings">
        <template #title>
            Site Settings
        </template>

        <template #description>
            These are the general settings for the web-app
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4 mb-3">
                <JetLabel for="site_name" value="Site Name"/>
                <JetInput id="site_name" v-model="form.siteName" type="text" class="mt-1 block w-full"/>
                <div class="mt-1 ml-1 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                    This will set the name of the site in the browser title bar.
                </div>
                <JetInputError :message="form.errors.siteName" class="mt-2"/>
            </div>

            <div class="col-span-6 grid grid-cols-2 gap-y-1 gap-x-4">
                <div>
                    <JetLabel for="system_shift_start_hour" value="Shift Start Hour"/>
                    <SelectField return-object-value id="system_shift_start_hour" v-model="form.systemShiftStartHour"
                                 :options="hours"
                                 class="mt-1"/>
                    <JetInputError :message="form.errors.systemShiftStartHour" class="mt-2"/>
                </div>
                <div>
                    <JetLabel for="system_shift_end_hour" value="Shift End Hour"/>
                    <SelectField return-object-value id="system_shift_end_hour" v-model="form.systemShiftEndHour" :options="hours"
                                 class="mt-1"/>
                    <JetInputError :message="form.errors.systemShiftEndHour" class="mt-2"/>
                </div>
                <div class="col-span-2 ml-1 text-sm text-gray-600 dark:text-gray-300">
                    The Start and End hours are used primarily to define the earliest and latest 'hour' a volunteer can make
                    themselves available for a shift (on their 'availability' page).
                </div>
            </div>
        </template>

        <template #actions>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </JetActionMessage>

            <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </JetButton>
        </template>
    </JetFormSection>
</template>
