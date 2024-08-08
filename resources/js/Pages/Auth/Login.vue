<script setup>
import Alert from '@/Components/Alert.vue';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import {computed, onMounted} from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const setPasswordSuccess = computed(() => usePage().props.jetstream.flash?.setPassword || '');

onMounted(() => {
    if (import.meta.env.DEV) {
        form.email = 'admin@example.com';
        form.password = 'password';
    }
});

</script>

<template>
    <Head title="Log in"/>

    <JetAuthenticationCard>
        <template #logo>
            <JetAuthenticationCardLogo/>
        </template>

        <JetValidationErrors class="mb-4"/>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <Alert v-if="setPasswordSuccess">
            {{ setPasswordSuccess }}
        </Alert>

        <form @submit.prevent="submit">
            <div>
                <JetLabel for="email" value="Email"/>
                <JetInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus/>
            </div>

            <div class="mt-4">
                <JetLabel for="password" value="Password"/>
                <JetInput id="password"
                          v-model="form.password"
                          type="password"
                          class="mt-1 block w-full"
                          required
                          autocomplete="current-password"/>
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <JetCheckbox v-model:checked="form.remember" name="remember"/>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-200">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword"
                      :href="route('password.request')"
                      class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-200 dark:hover-text-gray-500">
                    Forgot your password?
                </Link>

                <JetButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </JetButton>
            </div>
        </form>
    </JetAuthenticationCard>
</template>
