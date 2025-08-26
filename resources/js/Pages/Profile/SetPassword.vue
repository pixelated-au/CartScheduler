<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import JetAuthenticationCardLogo from "@/Jetstream/AuthenticationCardLogo.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";
import JetAuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
  editUser: {
    type: Object,
    required: true,
  },
  hashedEmail: {
    type: String,
    required: true,
  },
  siteName: {
    type: String,
    required: true,
  },
});

const form = useForm({
  password: "",
  password_confirmation: "",
  hashed_email: props.hashedEmail,
  user_id: props.editUser.id,
});

const submit = (setProcessing: (value: boolean) => void) => {
  form.post(route("set.password.update"), {
    onBefore: () => setProcessing(true),
    onFinish: () => {
      setProcessing(false);
      form.reset("password", "password_confirmation");
    },
  });
};
</script>

<template>
  <JetAuthLayout title="Set Password">
    <template #logo>
      <JetAuthenticationCardLogo />
    </template>

    <template #default="{ setProcessing }">
      <JetValidationErrors class="mb-4" />

      <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight mb-3">
        Welcome {{ editUser.name }}, to {{ siteName }}!
      </h1>

      <div class="text-gray-200">Please use the below form to set your password</div>

      <form @submit.prevent="submit(setProcessing)">
        <div class="mt-4">
          <JetLabel for="password" value="Password" />
          <PPassword id="password"
                     v-model="form.password"
                     class="block mt-1 w-full"
                     required
                     :inputProps="{ autocomplete: 'new-password' }" />
        </div>

        <div class="mt-4">
          <JetLabel for="password-confirmation" value="Confirm Password" />
          <PPassword id="password_confirmation"
                     v-model="form.password_confirmation"
                     :feedback="false"
                     type="password"
                     class="block mt-1 w-full"
                     required
                     :inputProps="{ autocomplete: 'new-password' }" />
        </div>

        <div class="flex items-center justify-end mt-4">
          <SubmitButton label="Set Password"
                        icon="iconify mdi--form-textbox-password"
                        :processing="form.processing"
                        :success="form.recentlySuccessful"
                        :failure="form.hasErrors"
                        :errors="form.errors"
                        type="submit" />
        </div>
      </form>
    </template>
  </JetAuthLayout>
</template>
