<script setup>
    // noinspection ES6UnusedImports
    import { VTooltip } from 'floating-vue'
    import 'floating-vue/dist/style.css'
    import { inject } from 'vue'

    const editor = inject('editor')

    const props = defineProps({
        action: {
            type: String,
            required: false,
        },
        actionProps: {
            type: Object,
            required: false,
            default: () => ({}),
        },
        tooltip: {
            type: String,
            required: true,
        },
    })

    const emit = defineEmits(['click'])

    const actionCall = (e) => {
        if (!props.action) {
            emit('click', e)
            return
        }

        console.log('action call', props.action, props.actionProps)
        const call = editor?.value?.chain()?.focus()[props?.action](props?.actionProps)?.run()
        console.log(call, props.actionProps)
        return call || (() => {
        })
    }
</script>
<template>
    <button type="button"
            v-tooltip="tooltip"
            class="rounded-sm p-1.5 mx-0.5 first:ml-0 border border-white last:mr-0 hover:bg-slate-300 hover:border-gray-400 dark:border-gray-500 dark:hover:bg-slate-700 dark:hover:border-gray-600"
            @click="actionCall">
        <slot></slot>
    </button>
</template>
