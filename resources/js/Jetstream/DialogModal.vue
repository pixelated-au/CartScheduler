<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal fill-screen :show="show" :max-width="maxWidth" :closeable="closeable" @close="close">
        <div class="px-6 py-4 overflow-y-auto flex-1 w-full">
            <div class="text-lg dark:bg-gray-900 dark:text-gray-100">
                <slot name="title"/>
            </div>

            <div class="mt-4 dark:bg-gray-900 dark:text-gray-100">
                <slot name="content"/>
            </div>
        </div>

        <div
            class="flex flex-row justify-end w-full px-6 py-4 bg-gray-100 dark:bg-gray-900 dark:text-gray-100 text-right">
            <slot name="footer"/>
        </div>
    </Modal>
</template>
