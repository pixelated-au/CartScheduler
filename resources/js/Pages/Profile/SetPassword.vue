<script setup>
    import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue'
    import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import JetValidationErrors from '@/Jetstream/ValidationErrors.vue'
    import { Head, useForm } from '@inertiajs/inertia-vue3'


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
    })


    const form = useForm({
        password: '',
        password_confirmation: '',
        hashed_email: props.hashedEmail,
        user_id: props.editUser.id,
    })

    const submit = () => {
        form.post(route('set.password.update'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        })
    }
</script>

<template>
    <Head title="Set Password"/>

    <JetAuthenticationCard>
        <template #logo>
            <JetAuthenticationCardLogo/>
        </template>

        <JetValidationErrors class="mb-4"/>

        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight mb-3">
            Welcome {{ editUser.name }}, to {{ siteName }}! </h1>

        <div class="text-gray-200">Please use the below form to set your password</div>

        <form @submit.prevent="submit">
            <div class="mt-4">
                <JetLabel for="password" value="Password"/>
                <JetInput id="password"
                          v-model="form.password"
                          type="password"
                          class="mt-1 block w-full"
                          required
                          autocomplete="new-password"/>
            </div>

            <div class="mt-4">
                <JetLabel for="password-confirmation" value="Confirm Password"/>
                <JetInput id="password-confirmation"
                          v-model="form.password_confirmation"
                          type="password"
                          class="mt-1 block w-full"
                          required
                          autocomplete="new-password"/>
            </div>

            <div class="flex items-center justify-end mt-4">
                <JetButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Set Password
                </JetButton>
            </div>
        </form>
    </JetAuthenticationCard>

</template>
