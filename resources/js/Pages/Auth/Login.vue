<script setup lang="ts">
import { useForm, usePage, Link  } from "@inertiajs/vue3";
import { computed, inject, onMounted } from "vue";
import Alert from "@/Components/Alert.vue";
import JetAuthenticationCardLogo from "@/Jetstream/AuthenticationCardLogo.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

interface PageProps {
  jetstream: {
    flash?: {
      setPassword?: string;
    };
  };
  [key: string]: unknown;
}

const props = defineProps<{
  canResetPassword: boolean;
  status?: string;
}>();

const form = useForm({
  email: "",
  password: "",
  remember: true,
});

const route = inject("route");

const submit = (): void => {
  if (!route) return;

  form.transform((data) => ({
    ...data,
    remember: form.remember ? "on" : "",
  })).post(route("login"), {
    onFinish: () => form.reset("password" as never),
  });
};

const setPasswordSuccess = computed<string>(() => {
  return usePage<PageProps>().props.jetstream.flash?.setPassword || "";
});

if (import.meta.env.DEV) {
  onMounted(() => {
    form.email = "admin@example.com";
    form.password = "password";
  });
};
</script>

<template>
  <AuthLayout title="Log in">
    <template #logo>
      <JetAuthenticationCardLogo />
    </template>

    <JetValidationErrors class="mb-4" />

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <Alert v-if="setPasswordSuccess">
      {{ setPasswordSuccess }}
    </Alert>

    <form @submit.prevent="submit">
      <div class="flex flex-col gap-3 [&>div]:flex [&>div]:flex-col [&>div]:gap-1">
        <div>
          <JetLabel for="email" value="Email" />
          <PInputText id="email"
                      v-model="form.email"
                      type="email"
                      class="block mt-1 w-full"
                      required
                      autocomplete="username"
                      autofocus />
        </div>

        <div>
          <JetLabel for="password" value="Password" />
          <PPassword input-id="password"
                     v-model="form.password"
                     :feedback="false"
                     toggle-mask
                     input-class="w-full"
                     required
                     autocomplete="current-password" />
        </div>

        <div class="mt-4 flex w-full !flex-row items-center">
          <PCheckbox binary input-id="remember" v-model="form.remember" name="remember" />
          <JetLabel for="remember">Remember me</JetLabel>
        </div>
      </div>

      <div class="flex gap-4 justify-end items-center mt-4">
        <Link v-if="props.canResetPassword"
              :href="route('password.request')"
              class="text-gray-600 underline hover:text-gray-900 dark:text-gray-200 dark:hover-text-gray-500">
          Forgot your password?
        </Link>

        <PButton label="Log in" icon="iconify mdi--login" icon-pos="right" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit" />
      </div>
    </form>
  </AuthLayout>
</template>
