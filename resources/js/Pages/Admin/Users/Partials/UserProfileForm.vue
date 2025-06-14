<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { computed, inject, ref, watch, nextTick } from "vue";
import useToast from "@/Composables/useToast.js";
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";

const props = defineProps({
    user: Object,
    action: {
        type: String,
        default: "edit",
    },
});

defineEmits([
    "cancel",
]);

const route = inject("route");

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
    is_unrestricted: props.user.is_unrestricted,
});

const updateUserData = () => {
    form.put(route("admin.users.update", props.user.id), {
        errorBag: "updateUserData",
        preserveScroll: true,
    });
    toast.success(`Data for ${props.user.name} was updated.`);
};

const createUserData = () => {
    form.post(route("admin.users.store"), {
        errorBag: "updateUserData",
        preserveScroll: true,
    });
};

const saveAction = () => {
    if (props.action === "edit") {
        updateUserData();
    } else {
        createUserData();
    }
};

const listRouteAction = () => {
    router.visit(route("admin.users.index"));
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
    router.delete(route("admin.users.destroy", props.user.id));
};

const performConfirmationAction = () => {
    if (modalDeleteAction.value) {
        doDeleteAction();
    } else {
        listRouteAction();
    }
};

const toast = useToast();

const performResendWelcomeAction = async () => {
    try {
        const response = await axios.post(route("admin.resend-welcome-email", { user_id: props.user.id }));
        toast.success(response.data.message);
    } catch (e) {
        toast.error(e.response.data.message, { timeout: 3000 });
    }
};

watch([() => form.is_unrestricted, () => form.role], ([isUnrestricted, role]) => {
    console.log("watch", isUnrestricted, role);
    if (!isUnrestricted && role === "admin") {
        nextTick(() => {
            form.role = "user";
            toast.warning("Restricted users cannot be administrators. The role has been changed to a standard user.");
        });
    }
});

const cancelButtonText = computed(() => form.isDirty ? "Cancel" : "Back");
</script>

<template>
<JetFormSection @submitted="saveAction">
  <template #title>
    Profile Information
  </template>

  <template #description>
    <div v-if="action === 'edit'">Update the information for {{ user.name }}.</div>
    <div v-else>Create a new user record</div>
    <PButton v-if="action === 'edit'"
             severity="info"
             variant="outlined"
             class="mt-5"
             @click="performResendWelcomeAction">
      <template v-if="user.has_logged_in">Send Password Reset Email</template>

      <template v-else>Resend Welcome Email</template>
    </PButton>
  </template>

  <template #form>
    <!-- Name -->
    <div class="col-span-6 sm:col-span-3">
      <JetLabel for="name" value="Name" />
      <PInputText id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name" />
      <JetInputError :message="form.errors.name" class="mt-2" />
    </div>

    <!-- Email -->
    <div class="col-span-6 sm:col-span-3">
      <JetLabel for="email" value="Email" />
      <PInputText id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
      <JetInputError :message="form.errors.email" class="mt-2" />
    </div>

    <!-- Mobile Phone -->
    <div class="col-span-6 sm:col-span-3">
      <JetLabel for="mobile-phone" value="Mobile Phone" />
      <PInputText id="mobile-phone" v-model="form.mobile_phone" type="tel" class="mt-1 block w-full" />
      <JetInputError :message="form.errors.mobile_phone" class="mt-2" />
    </div>

    <!-- Baptism Year -->
    <div class="col-span-6 sm:col-span-2">
      <JetLabel for="birth-year" value="Birth Year" />
      <PInputNumber id="birth-year"
                    v-model="form.year_of_birth"
                    :use-grouping="false"
                    :maxFractionDigits="0"
                    inputmode="numeric"
                    class="mt-1 block w-full" />
      <JetInputError :message="form.errors.year_of_birth" class="mt-2" />
    </div>

    <div class="my-3 col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div class="flex flex-col">
        <!-- Gender -->
        <JetLabel for="gender" value="Gender" />
        <PSelect name="gender"
                 id="gender"
                 placeholder="Select Gender"
                 v-model="form.gender"
                 option-label="label"
                 option-value="value"
                 :options="[
                   { label: 'Male', value: 'male' },
                   { label: 'Female', value: 'female' },
                 ]" />
        <JetInputError :message="form.errors.gender" class="mt-2" />
      </div>
      <div class="flex flex-col">
        <!-- Appointment -->
        <JetLabel for="appointment" value="Appointment" />
        <PSelect name="appointment"
                 id="appointment"
                 placeholder="Select An Appointment"
                 v-model="form.appointment"
                 option-label="label"
                 option-value="value"
                 :options="[
                   { label: 'None', value: null },
                   { label: 'Ministerial Servant', value: 'ministerial servant' },
                   { label: 'Elder', value: 'elder' },
                 ]" />
        <JetInputError :message="form.errors.appointment" class="mt-2" />
      </div>
      <div class="flex flex-col">
        <!-- Serving As -->
        <JetLabel for="serving_as" value="Serving As" />
        <!-- suppress SpellCheckingInspection -->
        <PSelect name="serving_as"
                 id="serving_as"
                 placeholder="Select A Privilege"
                 v-model="form.serving_as"
                 option-label="label"
                 option-value="value"
                 :options=" [
                   { label: 'Field Missionary', value: 'field missionary' },
                   { label: 'Special Pioneer', value: 'special pioneer' },
                   { label: 'Regular Pioneer', value: 'regular pioneer' },
                   { label: 'Circuit Overseer', value: 'circuit overseer' },
                   { label: 'Bethelite', value: 'bethel family member' },
                   { label: 'Publisher', value: 'publisher' },
                 ]" />
        <JetInputError :message="form.errors.serving_as" class="mt-2" />
      </div>
      <div class="flex flex-col">
        <!-- Marital Status -->
        <JetLabel for="marital_status" value="Marital Status" />
        <PSelect name="marital_status"
                 id="marital_status"
                 placeholder="Select A Status"
                 v-model="form.marital_status"
                 option-label="label"
                 option-value="value"
                 :options="[
                   { label: 'Single', value: 'single' },
                   { label: 'Married', value: 'married' },
                   { label: 'Separated', value: 'separated' },
                   { label: 'Divorced', value: 'divorced' },
                   { label: 'Widowed', value: 'widowed' },
                 ]" />
        <JetInputError :message="form.errors.marital_status" class="mt-2" />
      </div>
      <div v-if="user.spouse_name" class="self-center">
        <strong>
          Spouse: {{ user.spouse_name }}
        </strong>
      </div>
      <div class="sm:col-span-2 mt-3">
        <!-- Responsible Brother -->
        <JetLabel>
          <span class="flex items-center font-medium">Trained Responsible Brother: {{ form.responsible_brother ? "✅" : "❌" }}</span>
          <PCheckbox binary
                     v-model="form.responsible_brother"
                     value="responsible_brother"
                     name="responsible_brother" />
          <small class="ml-3 text-gray-700 dark:text-gray-300">
            Typically used to flag brothers who can be
            trusted to oversee a shift.
          </small>
        </JetLabel>
        <JetInputError :message="form.errors.responsible_brother" class="mt-2" />
      </div>
    </div>

    <div class="col-span-6 grid grid-cols-1 gap-6">
      <!-- Role -->
      <div class="card grid grid-cols-[max-content_1fr] items-center gap-x-4 gap-y-2">
        <JetLabel for="role" value="Role" />
        <PSelectButton name="role"
                       id="role"
                       v-model="form.role"
                       option-label="label"
                       option-value="value"
                       :options="[
                         { label: 'Standard User', value: 'user' },
                         { label: 'Administrator', value: 'admin' },
                       ]" />
        <JetInputError :message="form.errors.role" class="mt-2" />
        <div class="col-span-2 text-sm">
          <span class="font-medium italic">Warning:</span>
          Assigning an admin role to a user can grant them full access to the system.
        </div>
      </div>

      <!-- System Access -->
      <div class="card grid grid-cols-[max-content_1fr] items-center gap-x-4 gap-y-2">
        <JetLabel for="is-unrestricted" value="System Access" />
        <PSelectButton name="is-unrestricted"
                       id="is-unrestricted"
                       v-model="form.is_unrestricted"
                       option-label="label"
                       option-value="value"
                       :options="[
                         { label: 'Restricted', value: false },
                         { label: 'Unrestricted', value: true },
                       ]" />
        <JetInputError :message="form.errors.is_unrestricted" class="mt-2" />
        <div class="col-span-2 text-sm">
          <span class="font-medium italic">Restricted users</span>
          cannot self-roster and can only access shifts relevant to them.
          This includes their shift and the shifts either side of their shift.
        </div>
      </div>

      <!-- Account Status -->
      <div class="card grid grid-cols-[max-content_1fr] items-center gap-x-4 gap-y-2">
        <JetLabel for="is-enabled" value="Account Status" />
        <PSelectButton name="is-enabled"
                       id="is-enabled"
                       v-model="form.is_enabled"
                       option-label="label"
                       option-value="value"
                       :options="[
                         { label: 'Inactive', value: false },
                         { label: 'Active', value: true },
                       ]" />
        <JetInputError :message="form.errors.is_enabled" class="mt-2" />
        <div class="col-span-2 text-sm">
          <span class="font-medium italic">Inactive users</span> cannot log into or interact with the system.
        </div>
      </div>
    </div>
  </template>

  <template #actions>
    <PButton v-if="action === 'edit'"
             class="mr-auto"
             label="Delete"
             severity="danger"
             variant="outlined"
             :class="{ 'opacity-25': form.processing }"
             :disabled="form.processing"
             @click.prevent="onDelete" />

    <PButton :label="cancelButtonText"
             severity="secondary"
             variant="outlined"
             :class="{ 'opacity-25': form.processing }"
             :disabled="form.processing"
             @click.prevent="confirmCancel" />

    <PButton :class="{ 'opacity-25': form.processing }"
             :label="action === 'edit' ? 'Update' : 'Save'"
             :disabled="form.processing"
             @click.prevent="saveAction" />
  </template>

  <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
    Success: User Saved.
  </JetActionMessage>
</JetFormSection>

<JetConfirmationModal :show="showConfirmationModal">
  <template #title>Danger!</template>

  <template #content>
    <template v-if="modalDeleteAction">Are you sure you wish to delete this user?</template>

    <template v-else>Are you sure you wish to return? Your changes will be lost!</template>
  </template>

  <template #footer>
    <PButton label="Cancel" severity="secondary" variant="outlined" @click="showConfirmationModal = false" />
    <PButton severity="danger" label="Ok" @click="performConfirmationAction" />
  </template>
</JetConfirmationModal>
</template>
