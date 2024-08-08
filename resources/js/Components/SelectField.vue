<script setup>
import {Dropdown} from 'flowbite';
import {computed, inject, onMounted, ref, useSlots} from 'vue';

const props = defineProps({
    modelValue: [Object, Number, String],
    options: {
        type: Array,
        required: true,
    },
    emptyNote: String,
    selectLabel: String,
    returnObjectValue: {
        type: Boolean,
        default: false,
    },
    fullWidthButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const fieldUnique = computed(() => Math.random().toString(36).substring(2, 9));

const model = computed({
    get: () => props.modelValue,
    set: value => emit('update:modelValue', value),
});

const slots = useSlots();
const useLabelSlot = computed(() => !!slots.label);


const label = computed(() => isNaN(model.value) && model.value || model.value !== null
    ? props.options.find(option => option.value === model.value)?.label
    : props.selectLabel || 'Select one',
);

const noOptions = computed(() => props.options.length === 0);

const isDarkMode = inject('darkMode', false);

const arrowFill = computed(() => {
    if (isDarkMode.value) {
        return '#fff';
    }
    return noOptions.value ? '#000' : '#fff';
});

const buttonClasses = computed(() => {
    let classes = [];
    if (props.fullWidthButton) {
        classes.push('w-full');
    }
    if (noOptions.value) {
        classes.push('!bg-gray-300 dark:!bg-gray-700 !cursor-not-allowed');
    }
    return classes.join(' ');
});

const onSelect = (selection) => {
    dropdown.value.hide();
    if (noOptions.value) {
        return;
    }

    if (props.returnObjectValue) {
        // We're not returning the whole object, just the value
        console.log('selection value', selection.value);
        model.value = selection.value;
        return;
    }

    model.value = selection;
};

const selectedClass = (option) => {
    const val = props.returnObjectValue ? option.value : option;
    return model.value === val ? 'bg-purple-200 dark:bg-purple-800' : '';
};

const trigger = ref();
const target = ref();
const dropdown = ref();

onMounted(() => {
    dropdown.value = new Dropdown(target.value, trigger.value, {
        triggerType: 'click',
    });
});
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
             class="absolute z-10 w-64 bg-white rounded divide-y divide-gray-100 drop-shadow-xl dark:bg-gray-700 cursor-pointer hidden">
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                :aria-labelledby="`dropdown-select-${fieldUnique}`">
                <template v-if="noOptions">
                    <li class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-bold">
                        {{ emptyNote || 'No options available' }}
                    </li>
                </template>
                <template v-else>
                    <li v-for="option in options" :key="option.id" :class="selectedClass(option)">
                        <div
                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white font-bold"
                            :class="model === option.value ? 'bg-purple-100 dark:bg-purple-600 dark:text-white' : ''"
                            @click="onSelect(option)">
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
