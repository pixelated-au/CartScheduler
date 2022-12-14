<script setup>
    import { computed, ref } from 'vue'

    const props = defineProps({
        modelValue: Object,
        options: {
            type: Array,
            required: true,
        },
        selectLabel: String,
    })

    const emit = defineEmits(['update:modelValue'])

    const fieldUnique = computed(() => Math.random().toString(36).substring(2, 9))

    const isOpen = ref(false)

    const model = computed({
        get: () => props.modelValue,
        set: value => {
            isOpen.value = false
            emit('update:modelValue', value)
        },
    })

    const menuStyle = computed(() => {
        return isOpen.value ? 'display: block' : 'display: none'
    })

    const label = computed(() => model.value
        ? options.find(option => option.value === model.value)?.label
        : props.selectLabel || 'Select one')
</script>

<template>
    <div class="relative">
        <button id="dropdownDefault"
                :data-dropdown-toggle="`dropdown-${fieldUnique}`"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800"
                type="button"
                @click="isOpen = !isOpen">
            {{ label }}
            <svg class="ml-2 w-4 h-4"
                 aria-hidden="true"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <!-- Dropdown menu -->
        <div :id="`dropdown-${fieldUnique}`"
             class="absolute z-10 w-64 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 cursor-pointer"
             :style="menuStyle">
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefault">
                <li v-for="option in options" :key="option.id">
                    <div class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-bold"
                         @click="model = option">
                        {{ option.label }}
                        <slot name="extra" :option="option"></slot>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
