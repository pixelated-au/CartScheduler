<script setup>
import JetModal from "@/Jetstream/Modal.vue";

const props = defineProps({
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
})
const emit = defineEmits(['close'])
</script>

<template>
    <JetModal fill-screen :show="show" :maxWidth="maxWidth" :closeable="closeable" @close="$emit('close')">
        <slot/>
    </JetModal>
</template>

<style scoped lang="scss">

</style>
