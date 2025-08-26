<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import JetAuthenticationCardLogo from "@/Jetstream/AuthenticationCardLogo.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps<{
  email: string;
  token: string;
}>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: "",
  password_confirmation: "",
});

const submit = (setProcessing: (value: boolean) => void) => {
  form.post(route("password.update"), {
    onBefore: () => setProcessing(true),
    onFinish: () => {
      setProcessing(false);
      form.reset("password", "password_confirmation");
    },
  });
};
</script>

<template>
  <AuthLayout title="Reset Password">
    <template #logo>
      <JetAuthenticationCardLogo />
    </template>

    <template #default="{ setProcessing }">
      <JetValidationErrors class="mb-4" />

      <form @submit.prevent="submit(setProcessing)">
        <div>
          <JetLabel for="email" value="Email" />
          <PInputText id="email"
                      v-model="form.email"
                      type="email"
                      class="block mt-1 w-full"
                      autocomplete="username"
                      required
                      autofocus />
        </div>

        <div class="mt-4">
          <JetLabel for="password" value="Password" />
          <PPassword id="password"
                     v-model="form.password"
                     type="password"
                     class="block mt-1 w-full"
                     required
                     :inputProps="{ autocomplete: 'new-password' }"/>
        </div>

        <div class="mt-4">
          <JetLabel for="password_confirmation" value="Confirm Password" />
          <PPassword id="password_confirmation"
                     v-model="form.password_confirmation"
                     type="password"
                     class="block mt-1 w-full"
                     required
                     :inputProps="{ autocomplete: 'new-password' }"/>
        </div>

        <div class="flex items-center justify-end mt-4">
          <SubmitButton label="Reset Password"
                        icon="iconify mdi--form-textbox-password"
                        :processing="form.processing"
                        :success="form.recentlySuccessful"
                        :failure="form.hasErrors"
                        :errors="form.errors"
                        type="submit" />
        </div>
      </form>
    </template>
  </AuthLayout>
</template>
