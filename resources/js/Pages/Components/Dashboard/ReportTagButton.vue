<script setup>
    import { computed, ref } from 'vue'

    defineProps({
        name: String,
    })

    const emit = defineEmits(['toggled'])

    const isActive = ref(false)

    const toggleActive = () => {
        isActive.value = !isActive.value
        emit('toggled', isActive.value)
    }

    const classes = computed(() => {
        return {
            'bg-gray-200': !isActive.value,
            'text-gray-700': !isActive.value,
            'bg-green-200': isActive.value,
            'text-green-800': isActive.value,
        }
    })
</script>

<template>
    <button class="px-2.5 py-0.5 mr-1 rounded-full text-sm font-medium" :class="classes" @click="toggleActive">
            <span>
                {{ name }}
            </span>
    </button>

</template>
