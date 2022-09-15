<script setup>
    import VerticalRadioButtons from '@/Components/VerticalRadioButtons.vue'
    import JetActionMessage from '@/Jetstream/ActionMessage.vue'
    import JetButton from '@/Jetstream/Button.vue'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import JetFormSection from '@/Jetstream/FormSection.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetInputError from '@/Jetstream/InputError.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import { Inertia } from '@inertiajs/inertia'
    import { useForm } from '@inertiajs/inertia-vue3'
    import { computed, ref } from 'vue'

    const props = defineProps({
        user: Object,
        action: {
            type: String,
            default: 'edit',
        },
    })

    const emit = defineEmits([
        'cancel',
    ])

    const form = useForm({
        id: props.user.id,
        name: props.user.name,
        role: props.user.role,
        email: props.user.email,
        gender: props.user.gender,
        mobile_phone: props.user.mobile_phone,
        is_enabled: props.user.is_enabled,
    })

    const updateUserData = () => {
        form.put(route('admin.users.update', props.user.id), {
            errorBag: 'updateUserData',
            preserveScroll: true,
        })
    }

    const createUserData = () => {
        form.post(route('admin.users.store'), {
            errorBag: 'updateUserData',
            preserveScroll: true,
        })
    }

    const saveAction = () => {
        if (props.action === 'edit') {
            updateUserData()
        } else {
            createUserData()
        }
    }

    const listRouteAction = () => {
        Inertia.visit(route('admin.users.index'))
    }

    const showConfirmationModal = ref(false)
    const modalDeleteAction = ref(false)
    const confirmCancel = () => {
        modalDeleteAction.value = false
        if (form.isDirty) {
            showConfirmationModal.value = true
        } else {
            listRouteAction()
        }
    }

    const onDelete = () => {
        modalDeleteAction.value = true
        showConfirmationModal.value = true
    }

    const doDeleteAction = () => {
        Inertia.delete(route('admin.users.destroy', props.user.id))
    }

    const performConfirmationAction = () => {
        if (modalDeleteAction.value) {
            doDeleteAction()
        } else {
            listRouteAction()
        }
    }

    const cancelButtonText = computed(() => form.isDirty ? 'Cancel' : 'Back')
</script>

<template>
    <JetFormSection @submitted="saveAction">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update the user's personal information.
        </template>

        <template #form>
            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <JetLabel for="name" value="Name"/>
                <JetInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" autocomplete="name"/>
                <JetInputError :message="form.errors.name" class="mt-2"/>
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

            <!-- Role -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Role
                </div>
                <VerticalRadioButtons name="role" v-model="form.role" :options="[
                    { label: 'Administrator', value: 'admin' },
                    { label: 'Standard User', value: 'user' },
                ]"/>
                <JetInputError :message="form.errors.role" class="mt-2"/>
            </div>

            <!-- Activate User -->
            <div class="col-span-6 sm:col-span-4">
                <div class="font-medium text-sm text-gray-700">
                    Account Status
                </div>
                <VerticalRadioButtons name="is-enabled" v-model="form.is_enabled" :options="[
                    { label: 'Active', value: true },
                    { label: 'Inactive', value: false },
                ]"/>
                <JetInputError :message="form.errors.is_enabled" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <!-- Gender -->
                <div class="font-medium text-sm text-gray-700">
                    Gender
                </div>
                <VerticalRadioButtons name="gender" v-model="form.gender" :options="[
                    { label: 'Male', value: 'male' },
                    { label: 'Female', value: 'female' },
                ]"/>
                <JetInputError :message="form.errors.gender" class="mt-2"/>
            </div>
        </template>

        <template #actions>
            <div v-if="action === 'edit'" class="grow text-left">
                <JetButton outline
                           type="button"
                           style-type="warning"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="onDelete">
                    Delete
                </JetButton>
            </div>
            <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
                Success: User Saved.
            </JetActionMessage>

            <div>
                <JetButton class="mx-3"
                           type="button"
                           style-type="secondary"
                           :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="confirmCancel">
                    {{ cancelButtonText }}
                </JetButton>
                <JetButton :class="{ 'opacity-25': form.processing }"
                           :disabled="form.processing"
                           @click.prevent="saveAction">
                    Save
                </JetButton>
            </div>
        </template>
    </JetFormSection>

    <JetConfirmationModal :show="showConfirmationModal">
        <template #title>Danger!</template>
        <template #content>
            <template v-if="modalDeleteAction">Are you sure you wish to delete this user?</template>
            <template v-else>Are you sure you wish to return? Your changes will be lost!</template>
        </template>
        <template #footer>
            <JetButton class="mx-3" style-type="secondary" @click="showConfirmationModal = false">
                Cancel
            </JetButton>
            <JetButton style-type="warning" @click="performConfirmationAction">Ok</JetButton>
        </template>
    </JetConfirmationModal>
</template>
