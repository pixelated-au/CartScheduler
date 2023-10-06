<script setup>
import CloseCircle from "@/Components/Icons/CloseCircle.vue";
import QuestionCircle from "@/Components/Icons/QuestionCircle.vue";
import InputTextEIPField from "@/Components/InputTextEIPField.vue";
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import VacationDateRange from "@/Pages/Profile/Partials/VacationDateRange.vue";
import {useForm} from '@inertiajs/inertia-vue3';
// noinspection ES6UnusedImports
import {Dropdown as VDropdown, VTooltip} from 'floating-vue'

const props = defineProps({
    vacations: {
        type: Array,
        required: true,
    },
    userId: {
        type: Number,
        default: null,
    },
})
const form = useForm({
    vacations: props.vacations || [],
    deletedVacations: [],
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
        .put(route('update.user.vacations'), {
            preserveScroll: true,
            onSuccess: () => {
                form.defaults({
                    vacations: props.vacations || [],
                    deletedVacations: [],
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

const addVacation = () => form.vacations = [...form.vacations, {start_date: '', end_date: '', description: ''}];

const deleteVacation = (idx) => form.deletedVacations = [...form.deletedVacations, form.vacations.splice(idx, 1)[0]];

</script>

<template>
    <JetFormSection @submitted="update">
        <template #title>
            Vacations
        </template>

        <template #description>
            Use this to indicate when {{ props.userId ? 'this volunteer' : 'you' }} may be on holiday or unable to be
            rostered on.
        </template>

        <template #form>
            <div class="col-span-6 text-gray-700 dark:text-gray-100">
                <div v-if="form.vacations?.length"
                     class="border border-gray-200 dark:border-gray-900">
                    <div v-for="(vacation, idx) in form.vacations" :key="vacation.id"
                         class="bg-slate-200 dark:bg-slate-800 grid grid-cols-[auto_minmax(0,_1fr)] sm:grid-cols-[auto_minmax(0,_2fr)_3fr] gap-y-px gap-x-3 rounded p-3 items-center">
                        <JetButton style-type="transparent" type="button"
                                   class="row-span-2 sm:row-span-1 px-1 py-1 mr-2" @click="deleteVacation(idx)">
                            <CloseCircle/>
                        </JetButton>
                        <div>
                            <div class="font-bold">From - To</div>
                            <vacation-date-range v-model:start-date="vacation.start_date"
                                                 v-model:end-date="vacation.end_date"/>
                            <JetInputError :message="form.errors['vacations.' + idx + '.start_date']"/>
                            <JetInputError :message="form.errors['vacations.' + idx + '.end_date']"/>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            <div class="font-bold flex items-center">Comment
                                <v-dropdown v-if="vacation.id" class="ml-2 inline-block">
                                    <span><QuestionCircle/></span>
                                    <template #popper>
                                        <div class="">
                                            <p class="text-sm">Tap on the comment to edit it.</p>
                                        </div>
                                    </template>
                                </v-dropdown>
                            </div>
                            <JetInput v-if="!vacation.id" type="text" class="w-full dark:bg-slate-700"
                                      v-model="vacation.description"/>
                            <InputTextEIPField v-else input-class="w-full dark:bg-slate-700"
                                               v-model="vacation.description"
                                               empty-value="No comment set"/>
                            <JetInputError :message="form.errors['vacations.' + idx + '.description']"/>
                        </div>
                    </div>
                </div>
                <div v-else
                     class="items-stretch gap-y-px bg-slate-200 dark:bg-slate-800 border border-gray-200 dark:border-gray-900 rounded p-3 flex flex-wrap justify-center">
                    <div class="w-full text-center">No vacations set</div>
                </div>
                <div class="flex justify-center">
                    <JetButton style-type="primary" type="button" class="mt-3"
                               @click="addVacation">
                        Add a New Vacation
                    </JetButton>
                </div>
            </div>
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
