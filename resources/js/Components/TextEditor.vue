<script setup>
    import AlignButton from '@/Components/EditorToolbar/AlignButton.vue'
    import BoldButton from '@/Components/EditorToolbar/BoldButton.vue'
    import HeadingButton from '@/Components/EditorToolbar/HeadingButton.vue'
    import ItalicButton from '@/Components/EditorToolbar/ItalicButton.vue'
    import LinkSetButton from '@/Components/EditorToolbar/LinkSetButton.vue'
    import LinkUnsetButton from '@/Components/EditorToolbar/LinkUnsetButton.vue'
    import ListButton from '@/Components/EditorToolbar/ListButton.vue'
    import ParagraphButton from '@/Components/EditorToolbar/ParagraphButton.vue'
    import StrikethroughButton from '@/Components/EditorToolbar/StrikethroughButton.vue'
    import Link from '@tiptap/extension-link'
    import TextAlign from '@tiptap/extension-text-align'
    import StarterKit from '@tiptap/starter-kit'
    import { EditorContent, useEditor } from '@tiptap/vue-3'
    import { defineEmits, defineProps, provide, watch } from 'vue'

    const props = defineProps({
        modelValue: {
            type: String,
            default: '',
        },
    })

    const emit = defineEmits(['update:modelValue'])

    const editor = useEditor({
        content: props.modelValue,
        extensions: [
            StarterKit.configure({ heading: { levels: [3, 4, 5, 6] } }),
            Link.configure({ autolink: true, linkOnPaste: true, protocols: ['mailto'], openOnClick: false }),
            TextAlign.configure({ types: ['paragraph'] }),
        ],
        editable: true,
        onUpdate: (event) => {
            emit('update:modelValue', event.editor.getHTML())
        },
    })

    watch(() => props.modelValue, (value) => {
        if (!editor?.value || value === editor?.value?.getHTML()) {
            return
        }
        editor.value.commands.setContent(value, false)
    })

    provide('editor', editor)
</script>

<template>
    <div v-if="editor" class="border border-gray-300 rounded-md">
        <div class="border-b p-1">
            <HeadingButton :heading="3"/>
            <HeadingButton :heading="4"/>
            <HeadingButton :heading="5"/>
            <HeadingButton :heading="6"/>
            <ParagraphButton/>
            <BoldButton/>
            <ItalicButton/>
            <AlignButton direction="left"/>
            <AlignButton direction="right"/>
            <AlignButton direction="center"/>
            <AlignButton direction="justify"/>
            <StrikethroughButton/>
            <ListButton list-type="ordered"/>
            <ListButton list-type="unordered"/>
            <LinkSetButton/>
            <LinkUnsetButton/>
        </div>

        <editor-content :editor="editor" class="p-3 min-h-[300px]"/>
        <!--        <div>{{ modelValue }}</div>-->
    </div>

</template>


<style>
.ProseMirror {
    @apply p-3 focus-visible:rounded-md focus:border-0 focus-within:border-0 focus-visible:ring focus-visible:ring-1 focus-visible:ring-gray-500 focus-visible:ring-opacity-50;
}

.ProseMirror:focus {
    outline: none;
    border: 0;
}
</style>
