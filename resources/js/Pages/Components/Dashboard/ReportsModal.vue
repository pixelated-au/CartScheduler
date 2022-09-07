<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import ConfirmationModal from '@/Jetstream/ConfirmationModal.vue'

    const emit = defineEmits(['close'])

    defineProps({
        show: {
            type: Boolean,
            default: false,
        },
    })

    const close = () => {
        emit('close')
    }
</script>

<template>
    <ConfirmationModal :show="show" @close="close">
        <template #title>
            <h3>Outstanding Reports</h3>
        </template>

        <template #content>
            <div class="">
                <slot/>
            </div>
        </template>

        <template #footer>
            <JetButton type="button" style-type="secondary" @click="close">Close</JetButton>
        </template>
    </ConfirmationModal>
</template>
