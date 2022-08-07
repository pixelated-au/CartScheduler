<script setup>
    import { computed } from 'vue'

    const props = defineProps({
        name: String,
        modelValue: String | Number,
        /**
         * @typedef {label: string, value: string|number|boolean}[]
         */
        options: Array,
    })

    const emit = defineEmits([
        'update:modelValue',
    ])

    /**
     * @typedef {label: string, value: string|number|boolean}[]
     */
    const vModel = computed({
        get: () => props.modelValue,
        set: (val) => {
            emit('update:modelValue', val)
        },
    })

</script>


<template>
    <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 sm:flex dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        <li v-for="option in options"
            :key="option.value"
            class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r dark:border-gray-600">
            <div class="flex items-center pl-3">
                <!--                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">-->
                <input type="radio"
                       :id="`input-${name}-${option.value}`"
                       :name="name"
                       :value="option.value"
                       v-model="vModel"
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                <!--                                   class="py-3 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">-->
                <label :for="`input-${name}-${option.value}`"
                       class="py-3 ml-2 w-full text-sm font-medium text-gray-900">
                    {{ option.label }}
                </label>
            </div>
        </li>
    </ul>
</template>
