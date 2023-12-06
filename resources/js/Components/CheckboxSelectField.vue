<script setup>
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import {Dropdown} from 'flowbite'
import {computed, inject, onMounted, ref, useSlots} from 'vue'

const props = defineProps({
    modelValue: Object,
    emptyNote: String,
    fullWidthButton: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue'])

const fieldUnique = computed(() => Math.random().toString(36).substring(2, 9))

const model = computed({
    get: () => props.modelValue,
    set: value => emit('update:modelValue', value),
})

const slots = useSlots()
const useLabelSlot = computed(() => !!slots.label)


const label = computed(() => {
    if (!props.modelValue) {
        return '0 selected'
    }
    let count = 0
    for (const key in model.value) {
        if (model.value[key].value) {
            count++
        }
    }
    return `${count} selected`
})

const noOptions = computed(() => Object.entries(props.modelValue).length === 0)

const isDarkMode = inject('darkMode', false)

const arrowFill = computed(() => {
    if (isDarkMode.value) {
        return '#fff'
    }
    return noOptions.value ? '#000' : '#fff'
})

const buttonClasses = computed(() => {
    let classes = []
    if (props.fullWidthButton) {
        classes.push('w-full')
    }
    if (noOptions.value) {
        classes.push('!bg-gray-300 dark:!bg-gray-700 !cursor-not-allowed')
    }
    return classes.join(' ')
})

const onSelect = (name) => {
    if (noOptions.value) {
        return
    }
    model.value[name].value = !model.value[name].value
}

const trigger = ref()
const target = ref()
const dropdown = ref()

onMounted(() => {
    dropdown.value = new Dropdown(target.value, trigger.value, {
        triggerType: 'click',
    })
})

const modelLength = computed(() => Object.keys(model.value).length)

const liClasses = index => ({
    "rounded-b": index === modelLength.value - 1,
    "rounded-t": index === 0,
})
</script>

<template>
    <div class="relative">
        <button :ref="el => trigger = el"
                :id="`dropdown-select-${fieldUnique}`"
                :data-dropdown-toggle="`dropdown-${fieldUnique}`"
                :class="buttonClasses"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800"
                type="button" data-on-show="foo">
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
        <div :ref="el => target = el"
             :id="`dropdown-${fieldUnique}`"
             class="border border-gray-200 dark:border-gray-800 absolute z-10 w-64 bg-white rounded divide-y divide-gray-100 drop-shadow-xl dark:bg-gray-700 cursor-pointer hidden">
            <ul class="text-sm text-gray-700 dark:text-gray-200"
                :aria-labelledby="`dropdown-select-${fieldUnique}`">
                <li v-for="(value, key, index) in model" :key="key"
                    class="border-b border-b-gray-200 dark:border-gray-800 last:border-b-0">
                    <label
                        class="flex items-center py-2 pl-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-normal"
                        :class="liClasses(index)">
                        <JetCheckbox v-model:checked="value.value" value="true" class="mr-2"/>
                        {{ value.label }}
                    </label>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
