<script setup>
    import VerticalRadioButtons from '@/Components/VerticalRadioButtons.vue'
    import JetActionMessage from '@/Jetstream/ActionMessage.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetFormSection from '@/Jetstream/FormSection.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import { useForm } from '@inertiajs/inertia-vue3'

    const props = defineProps({
        user: Object,
    })

    const form = useForm({
        _method: 'PUT',
        name: props.user.name,
        role: props.user.role,
        email: props.user.email,
        gender: props.user.gender,
        mobile_phone: props.user.mobile_phone,
        is_active: props.user.is_active,
    })

    const updateUserData = () => {
        form.post(route('user-profile-information.update'), {
            errorBag: 'updateProfileInformation',
            preserveScroll: true,
            // onSuccess: () => {},
        })
    }

</script>

<template>
    <JetFormSection class="bg-gray-200" @submitted="updateUserData">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update the user's personal information.


            <!-- Activate User -->
            <div class="mt-10">
                <div class="font-medium text-sm text-gray-700">
                    Account Status
                </div>
                <VerticalRadioButtons name="is-active" v-model="form.is_active" :options="[
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ]"/>
            </div>


        </template>

        <template #form>
            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="name" value="Name"/>
                <JetInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name"/>
                <JetInputError :message="form.errors.name" class="mt-2"/>
            </div>

            <!-- Role -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Role
                </div>
                <VerticalRadioButtons name="role" v-model="form.role" :options="[
                    { label: 'Administrator', value: 'admin' },
                    { label: 'Standard User', value: 'user' },
                ]"/>
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="email" value="Email"/>
                <JetInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.email" class="mt-2"/>
            </div>

            <!-- Mobile Phone -->
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="mobile-phone" value="Mobile Phone"/>
                <JetInput id="mobile-phone" v-model="form.mobile_phone" type="tel" class="mt-1 block w-full"/>
                <JetInputError :message="form.errors.mobile_phone" class="mt-2"/>
            </div>

            <!-- Gender -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Gender
                </div>
                <VerticalRadioButtons name="gender" v-model="form.gender" :options="[
                    { label: 'Male', value: 'male' },
                    { label: 'Female', value: 'female' },
                ]"/>
            </div>

        </template>

        <template #actions>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </JetActionMessage>

            <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </JetButton>
        </template>
    </JetFormSection>
</template>
