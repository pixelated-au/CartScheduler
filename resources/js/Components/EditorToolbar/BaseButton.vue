<script>
import {VTooltip} from 'floating-vue';

export default {
    directives: {
        tooltip: VTooltip,
    },
};
</script>

<script setup>
import {inject} from 'vue';

const editor = inject('editor');

const props = defineProps({
    action: {
        type: String,
        required: false,
    },
    actionProps: {
        type: [Object, String],
        required: false,
        default: null,
    },
    tooltip: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['click']);

const actionCall = (e) => {
    if (!props.action) {
        emit('click', e);
    }

    const call = editor?.value?.chain()?.focus()[props?.action](props?.actionProps || undefined)?.run();
    return call || (() => {
    });
};
</script>
<template>
    <button type="button"
            v-tooltip="tooltip"
            class="rounded-sm p-1.5 mx-0.5 first:ml-0 border border-white last:mr-0 hover:bg-slate-300 hover:border-gray-400 dark:border-gray-500 dark:hover:bg-slate-700 dark:hover:border-gray-600"
            @click="actionCall">
        <slot></slot>
    </button>
</template>
