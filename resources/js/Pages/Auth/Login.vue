<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, inject, onMounted } from "vue";
import Alert from "@/Components/Alert.vue";
import JetAuthenticationCard from "@/Jetstream/AuthenticationCard.vue";
import JetAuthenticationCardLogo from "@/Jetstream/AuthenticationCardLogo.vue";
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: true,
});

const route = inject("route");

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};

const setPasswordSuccess = computed(() => usePage().props.jetstream.flash?.setPassword || "");

if (import.meta.env.DEV) {
    onMounted(() => {
        form.email = "admin@example.com";
        form.password = "password";
    });
}
</script>

<template>
<Head title="Log in"/>

<JetAuthenticationCard>
  <template #logo>
    <JetAuthenticationCardLogo/>
  </template>

  <JetValidationErrors class="mb-4"/>

  <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
    {{ status }}
  </div>

  <Alert v-if="setPasswordSuccess">
    {{ setPasswordSuccess }}
  </Alert>

  <form @submit.prevent="submit">
    <div class="flex flex-col gap-3 [&>div]:flex [&>div]:flex-col [&>div]:gap-1">
      <div>
        <FormLabel for="email" value="Email"/>
        <PInputText id="email" v-model="form.email" type="email" class="block mt-1 w-full" required autocomplete="username" autofocus/>
      </div>

      <div>
        <FormLabel for="password" value="Password"/>
        <PPassword
            input-id="password"
            v-model="form.password"
            :feedback="false"
            toggle-mask
            input-class="w-full"
            required
            autocomplete="current-password"/>
      </div>

      <div class="mt-4">
        <label class="flex items-center">
          <JetCheckbox v-model:checked="form.remember" name="remember"/>
          <span class="ml-2 text-gray-600 dark:text-gray-200">Remember me</span>
        </label>
      </div>
    </div>

    <div class="flex gap-4 justify-end items-center mt-4">
      <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="text-gray-600 underline hover:text-gray-900 dark:text-gray-200 dark:hover-text-gray-500">
        Forgot your password?
      </Link>

      <PButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
        Log in
      </PButton>
    </div>
  </form>
</JetAuthenticationCard>
</template>
