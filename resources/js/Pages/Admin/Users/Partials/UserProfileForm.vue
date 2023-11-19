<script setup>
import SelectField from "@/Components/SelectField.vue";
import VerticalRadioButtons from '@/Components/VerticalRadioButtons.vue'
import useToast from '@/Composables/useToast.js'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetCheckbox from '@/Jetstream/Checkbox.vue'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import {Inertia} from '@inertiajs/inertia'
import {useForm} from '@inertiajs/inertia-vue3'
import {computed, ref} from 'vue'

const props = defineProps({
  user: Object,
  action: {
    type: String,
    default: 'edit',
  },
})

const emit = defineEmits([
  'cancel',
])

const form = useForm({
  id: props.user.id,
  name: props.user.name,
  role: props.user.role,
  email: props.user.email,
  gender: props.user.gender,
  mobile_phone: props.user.mobile_phone,
  birth_year: props.user.birth_year,
  year_of_birth: props.user.year_of_birth,
  marital_status: props.user.marital_status,
  appointment: props.user.appointment,
  serving_as: props.user.serving_as,
  responsible_brother: props.user.responsible_brother,
  is_enabled: props.user.is_enabled,
})

const updateUserData = () => {
  form.put(route('admin.users.update', props.user.id), {
    errorBag: 'updateUserData',
    preserveScroll: true,
  })
}

const createUserData = () => {
  form.post(route('admin.users.store'), {
    errorBag: 'updateUserData',
    preserveScroll: true,
  })
}

const saveAction = () => {
  if (props.action === 'edit') {
    updateUserData()
  } else {
    createUserData()
  }
}

const listRouteAction = () => {
  Inertia.visit(route('admin.users.index'))
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
  Inertia.delete(route('admin.users.destroy', props.user.id))
}

const performConfirmationAction = () => {
  if (modalDeleteAction.value) {
    doDeleteAction()
  } else {
    listRouteAction()
  }
}

const toast = useToast()

const performResendWelcomeAction = async () => {
  try {
    const response = await axios.post(route('admin.resend-welcome-email', {user_id: props.user.id}))
    toast.success(response.data.message)
  } catch (e) {
    toast.error(e.response.data.message, {timeout: 3000})
  }

}

const cancelButtonText = computed(() => form.isDirty ? 'Cancel' : 'Back')
</script>

<template>
    <JetFormSection @submitted="saveAction">
        <template #title>
            Profile Information
        </template>

        <template #description>
            <div>Update the user's personal information.</div>
            <JetButton outline class="mt-5" style-type="info" @click="performResendWelcomeAction">
                <template v-if="user.has_logged_in">Send Password Reset Email</template>
                <template v-else>Resend Welcome Email</template>
            </JetButton>

        </template>

        <template #form>
            <!-- Name -->
            <div class="col-span-6 sm:col-span-3">
                <JetLabel for="name" value="Name"/>
                <JetInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name"/>
                <JetInputError :message="form.errors.name" class="mt-2"/>
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-3">
                <JetLabel for="email" value="Email"/>
                <JetInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.email" class="mt-2"/>
            </div>

            <!-- Mobile Phone -->
            <div class="col-span-6 sm:col-span-3">
                <JetLabel for="mobile-phone" value="Mobile Phone"/>
                <JetInput id="mobile-phone" v-model="form.mobile_phone" type="tel" class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.mobile_phone" class="mt-2"/>
            </div>

            <!-- Baptism Year -->
            <div class="col-span-6 sm:col-span-2">
                <JetLabel for="birth-year" value="Birth Year"/>
                <JetInput id="birth-year" v-model="form.year_of_birth" type="number" inputmode="numeric"
                          class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.year_of_birth" class="mt-2"/>
            </div>

            <div class="my-3 col-span-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <!-- Gender -->
                    <div class="font-medium text-gray-700 dark:text-gray-100">
                        Gender
                    </div>
                    <SelectField return-object-value name="gender" v-model="form.gender" :options="[
                    { label: 'Male', value: 'male' },
                    { label: 'Female', value: 'female' },
                ]"/>
                    <JetInputError :message="form.errors.gender" class="mt-2"/>
                </div>
                <div>
                    <!-- Appointment -->
                    <div class="font-medium text-gray-700 dark:text-gray-100">
                        Appointment
                    </div>
                    <SelectField return-object-value name="appointment" v-model="form.appointment" :options="[
                    { label: 'None', value: null },
                    { label: 'Ministerial Servant', value: 'ministerial servant' },
                    { label: 'Elder', value: 'elder' },
                ]"/>
                    <JetInputError :message="form.errors.appointment" class="mt-2"/>
                </div>
                <div>
                    <!-- Serving As -->
                    <div class="font-medium text-gray-700 dark:text-gray-100">
                        Serving As
                    </div>
                    <!--suppress SpellCheckingInspection -->
                    <SelectField return-object-value name="serving_as" v-model="form.serving_as" :options=" [
                    { label: 'Field Missionary', value: 'field missionary' },
                    { label: 'Special Pioneer', value: 'special pioneer' },
                    { label: 'Regular Pioneer', value: 'regular pioneer' },
                    { label: 'Circuit Overseer', value: 'circuit overseer' },
                    { label: 'Bethelite', value: 'bethel family member' },
                    { label: 'Publisher', value: 'publisher' },
                ]"/>
                    <JetInputError :message="form.errors.serving_as" class="mt-2"/>
                </div>
                <div>
                    <!-- Marital Status -->
                    <div class="font-medium text-gray-700 dark:text-gray-100">
                        Marital Status
                    </div>
                    <SelectField return-object-value name="marital_status" v-model="form.marital_status" :options="[
                    { label: 'Single', value: 'single' },
                    { label: 'Married', value: 'married' },
                    { label: 'Separated', value: 'separated' },
                    { label: 'Divorced', value: 'divorced' },
                    { label: 'Widowed', value: 'widowed' },
                ]"/>
                    <JetInputError :message="form.errors.marital_status" class="mt-2"/>
                </div>
                <div v-if="user.spouse_name" class="self-center">
                    <div class="font-medium text-gray-700 dark:text-gray-100">
                        Spouse: {{ user.spouse_name }}
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <!-- Responsible Brother -->
                    <label class="font-medium text-gray-700 dark:text-gray-100">
                        <div>Trained Responsible Brother</div>
                        <JetCheckbox v-model:checked="form.responsible_brother" value="responsible_brother"
                                     name="responsible_brother"/>
                        <small class="ml-3 text-gray-700 dark:text-gray-300">Typically used to flag brothers who can be
                            trusted to oversee a shift.</small>
                    </label>
                    <JetInputError :message="form.errors.responsible_brother" class="mt-2"/>
                </div>
            </div>

            <!-- Role -->
            <div class="col-span-6 sm:col-span-3">
                <div class="font-medium text-gray-700 dark:text-gray-100">
                    Role
                </div>
                <VerticalRadioButtons name="role" v-model="form.role" :options="[
                    { label: 'Administrator', value: 'admin' },
                    { label: 'Standard User', value: 'user' },
                ]"/>
                <JetInputError :message="form.errors.role" class="mt-2"/>
            </div>

            <!-- Activate User -->
            <div class="col-span-6 sm:col-span-3">
                <div class="font-medium text-gray-700 dark:text-gray-100">
                    Account Status
                </div>
                <VerticalRadioButtons name="is-enabled" v-model="form.is_enabled" :options="[
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ]"/>
                <JetInputError :message="form.errors.is_enabled" class="mt-2"/>
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
                Success: User Saved.
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
