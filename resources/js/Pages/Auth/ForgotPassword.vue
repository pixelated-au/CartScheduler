<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import JetAuthenticationCardLogo from "@/Jetstream/AuthenticationCardLogo.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

defineProps<{
  status: string;
}>();

const form = useForm({
  email: "",
});

const submit = (setProcessing: (value: boolean) => void) => {
  form.post(route("password.email"), {
    onBefore: () => setProcessing(true),
    onFinish: () => setProcessing(false),
  });
};
</script>

<template>
  <AuthLayout title="Forgot Password">
    <template #logo>
      <JetAuthenticationCardLogo />
    </template>

    <template #default="{ setProcessing }">
      <div class="flex flex-col">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-200">
          Forgot your password? No problem. Just let us know your email address and we will email you a password reset
          link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
          {{ status }}
        </div>

        <JetValidationErrors class="mb-4" />

        <form @submit.prevent="submit(setProcessing)">
          <div class="flex flex-col gap-3 [&>div]:flex [&>div]:flex-col [&>div]:gap-1">
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
          </div>

          <div>
            <div class="flex gap-4 justify-end items-center mt-4">
              <Link :href="route('login')"
                    class="text-gray-600 underline hover:text-gray-900 dark:text-gray-200 dark:hover-text-gray-500">
                Return to login
              </Link>
              <SubmitButton label="Email Password Reset Link"
                            icon="iconify mdi--email"
                            :processing="form.processing"
                            :success="form.recentlySuccessful"
                            :failure="form.hasErrors"
                            :errors="form.errors"
                            type="submit" />
            </div>
          </div>
        </form>
      </div>
    </template>
  </AuthLayout>
</template>
