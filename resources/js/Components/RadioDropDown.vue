<script setup>
    import { computed, defineEmits, defineProps, onMounted } from 'vue'

    const props = defineProps({
        modelValue: {},
        options: {
            type: Array,
            required: true,
        },
    })

    const emit = defineEmits([
        'update:modelValue',
    ])

    const myModel = computed({
        get () {
            return props.modelValue
        },
        set (value) {
            emit('update:modelValue', value)
        },
    })

    const buttonLabel = computed(() => {
        console.log(myModel.value)
        return props.options.find(option => option.value === myModel.value)?.label || 'Please select'
    })
    const compId = Math.random().toString(36).substring(2, 9)

    const initListeners = () => {
        document.querySelectorAll('[data-dropdown-toggle]').forEach(triggerEl => {
            const targetEl = document.getElementById(triggerEl.getAttribute('data-dropdown-toggle'))
            const placement = triggerEl.getAttribute('data-dropdown-placement')

            new Dropdown(targetEl, triggerEl, {
                placement: placement ? placement : 'bottom',
            })
        })
    }

    onMounted(() => initListeners())

</script>

<template>
    <button :id="`${compId}-dropdown-radio-button`"
            :data-dropdown-toggle="`${compId}-dropdown-radio`"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            type="button">{{ buttonLabel }}
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
    <div :id="`${compId}-dropdown-radio`"
         class="z-10 w-48 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 hidden"
         style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(319px, 70px, 0px);"
         data-popper-reference-hidden=""
         data-popper-escaped=""
         data-popper-placement="bottom">
        <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownRadioBgHoverButton">
            <li>
                <div v-for="(option, index) in options"
                     :key="index"
                     class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input :id="`${compId}-radio-${index}`"
                           type="radio"
                           v-model="myModel"
                           :value="option.value"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label :for="`${compId}-radio-${index}`"
                           class="ml-2 w-full text-sm font-medium text-gray-900 rounded dark:text-gray-300">
                        {{ option.label }}
                    </label>
                </div>
            </li>
            <!--            <li>-->
            <!--                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">-->
            <!--                    <input checked="" id="default-radio-5" type="radio" value="" name="default-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">-->
            <!--                    <label for="default-radio-5" class="ml-2 w-full text-sm font-medium text-gray-900 rounded dark:text-gray-300">Checked-->
            <!--                        state</label>-->
            <!--                </div>-->
            <!--            </li>-->
            <!--            <li>-->
            <!--                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">-->
            <!--                    <input id="default-radio-6" type="radio" value="" name="default-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">-->
            <!--                    <label for="default-radio-6" class="ml-2 w-full text-sm font-medium text-gray-900 rounded dark:text-gray-300">Default-->
            <!--                        radio</label>-->
            <!--                </div>-->
            <!--            </li>-->
        </ul>
    </div>
</template>

<!--<style lang="css">--><!--button[data-popper-reference-hidden] {--><!--    visibility: hidden;--><!--    pointer-events: none;--><!--}--><!--</style>-->
