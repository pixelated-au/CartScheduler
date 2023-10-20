<script setup>
    import {computed, inject, ref, useSlots} from 'vue'

    const props = defineProps({
        modelValue: Object,
        options: {
            type: Array,
            required: true,
        },
        emptyNote: String,
        selectLabel: String,
        emitFullObject: {
            type: Boolean,
            default: false,
        }
    })

    const emit = defineEmits(['update:modelValue'])

    const fieldUnique = computed(() => Math.random().toString(36).substring(2, 9))

    const isOpen = ref(false)

    const model = computed({
        get: () => props.modelValue,
        set: value => {
            isOpen.value = false
            emit('update:modelValue', props.emitFullObject ? value : value.value)
        },
    })

    const menuStyle = computed(() => {
        return isOpen.value ? 'display: block' : 'display: none'
    })

    const slots = useSlots()
    const useLabelSlot = computed(() => !!slots.label)


    const label = computed(() => model.value
        ? props.options.find(option => option.value === model.value)?.label
        : props.selectLabel || 'Select one'
    )

    const noOptions = computed(() => props.options.length === 0)

    const isDarkMode = inject('darkMode', false)

    const arrowFill = computed(() => {
        if (isDarkMode.value) {
            return '#fff'
        }
        return noOptions.value ? '#000' : '#fff'
    })

    const buttonClasses = computed(() => {
        let classes = []
        if (noOptions.value) {
            classes.push('!bg-gray-300 dark:!bg-gray-700 !cursor-not-allowed')
        }
        return classes.join(' ')
    })

</script>

<template>
    <div class="relative">
        <button id="dropdownDefault"
                :data-dropdown-toggle="`dropdown-${fieldUnique}`"
                :class="buttonClasses"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800"
                type="button"
                @click="isOpen = !isOpen">
            <slot name="label" v-if="useLabelSlot"/>
            <template v-else>
                {{ label }}
            </template>
            <svg class="ml-2 w-4 h-4"
                 aria-hidden="true"
                 fill="none"
                 viewBox="0 0 24 24"
                 :stroke="arrowFill"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <!-- Dropdown menu -->
        <div :id="`dropdown-${fieldUnique}`"
             class="absolute z-10 w-64 bg-white rounded divide-y divide-gray-100 drop-shadow-xl dark:bg-gray-700 cursor-pointer"
             :style="menuStyle">
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefault">
                <template v-if="noOptions">
                    <li class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-bold">
                        {{ emptyNote || 'No options available' }}
                    </li>
                </template>
                <template v-else>
                    <li v-for="option in options" :key="option.id">
                        <div
                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-bold"
                            :class="model === option.value ? 'bg-purple-100 dark:bg-purple-600 dark:text-white' : ''"
                            @click="model = option">
                            {{ option.label }}
                            <slot name="extra" :option="option"></slot>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
