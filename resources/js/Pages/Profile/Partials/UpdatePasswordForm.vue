<script setup>
import { useForm } from "@inertiajs/vue3";
import { useTemplateRef, inject } from "vue";
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";

const passwordInput = useTemplateRef("passwordInput");
const currentPasswordInput = useTemplateRef("currentPasswordInput");

const route = inject("route");

const form = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const updatePassword = () => {
  form.put(route("user-password.update"), {
    errorBag: "updatePassword",
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset("password", "password_confirmation");
        passwordInput.value.focus();
      }

      if (form.errors.current_password) {
        form.reset("current_password");
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <JetFormSection @submitted="updatePassword">
    <template #title>
      Update Password
    </template>

    <template #description>
      To ensure your account is secure, you can use a long password.
    </template>

    <template #form>
      <div class="col-span-6 sm:col-span-4">
        <JetLabel for="current_password" value="Current Password" />
        <JetInput
            id="current_password"
            ref="currentPasswordInput"
            v-model="form.current_password"
            type="password"
            class="mt-1 block w-full"
            autocomplete="current-password" />
        <JetInputError :message="form.errors.current_password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <JetLabel for="password" value="New Password" />
        <JetInput
            id="password"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            class="mt-1 block w-full"
            autocomplete="new-password" />
        <JetInputError :message="form.errors.password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <JetLabel for="password_confirmation" value="Confirm Password" />
        <JetInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="mt-1 block w-full"
            autocomplete="new-password" />
        <JetInputError :message="form.errors.password_confirmation" class="mt-2" />
      </div>
    </template>

    <template #actions>
      <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
        Saved.
      </JetActionMessage>

      <PButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Save
      </PButton>
    </template>
  </JetFormSection>
</template>
