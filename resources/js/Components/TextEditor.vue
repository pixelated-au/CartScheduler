<script setup>
import AlignButton from '@/Components/EditorToolbar/AlignButton.vue';
import BoldButton from '@/Components/EditorToolbar/BoldButton.vue';
import HeadingButton from '@/Components/EditorToolbar/HeadingButton.vue';
import ItalicButton from '@/Components/EditorToolbar/ItalicButton.vue';
import LinkSetButton from '@/Components/EditorToolbar/LinkSetButton.vue';
import LinkUnsetButton from '@/Components/EditorToolbar/LinkUnsetButton.vue';
import ListButton from '@/Components/EditorToolbar/ListButton.vue';
import ParagraphButton from '@/Components/EditorToolbar/ParagraphButton.vue';
import StrikethroughButton from '@/Components/EditorToolbar/StrikethroughButton.vue';
import { SyntaxHighlight } from '@/Components/TextEditorSyntaxHighlighter';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';
import StarterKit from '@tiptap/starter-kit';
import {EditorContent, useEditor} from '@tiptap/vue-3';
import {provide, watch} from 'vue';
import { serialize, deserialize } from "./TextEditorMarkdown"

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    highlightSyntax: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

let editor_extensions = [
    StarterKit.configure({heading: {levels: [3, 4, 5, 6]}}),
    Link.configure({autolink: true, linkOnPaste: true, protocols: ['mailto'], openOnClick: false}),
    TextAlign.configure({types: ['paragraph']}),
]

if (props.highlightSyntax)
    editor_extensions.push(SyntaxHighlight)

const editor = useEditor({
    content: deserialize("", props.modelValue),
    extensions: editor_extensions,
    editable: true,
    onUpdate: (event) => {
        emit('update:modelValue', serialize(editor.value.view.state.schema, editor.value.getJSON()));
    },
    onCreate: (event) => {
        event.editor.content = deserialize(event.editor.schema, props.modelValue)
        //let markdown = serialize(editor.value.view.state.schema, editor.value.getJSON());
    }
});

watch(() => props.modelValue, (value) => {
    if (!editor?.value || value === editor?.value) {
        return;
    }
    //let new_html = deserialize(editor.value.view.state.schema, value)
    //editor.value.commands.setContent(new_html, false);
    //let markdown = serialize(editor.value.view.state.schema, editor.value.getJSON())
    //editor.value.commands.setContent(markdown, false);
});

provide('editor', editor);
</script>

<template>
    <div v-if="editor" class="border border-gray-300 dark:border-gray-700 rounded-md">
        <div class="border-b p-1 dark:border-gray-500">
            <HeadingButton :heading="3"/>
            <HeadingButton :heading="4"/>
            <HeadingButton :heading="5"/>
            <HeadingButton :heading="6"/>
            <ParagraphButton/>
            <BoldButton/>
            <ItalicButton/>
            <StrikethroughButton/>
            <AlignButton direction="left"/>
            <AlignButton direction="right"/>
            <AlignButton direction="center"/>
            <AlignButton direction="justify"/>
            <ListButton list-type="ordered"/>
            <ListButton list-type="unordered"/>
            <LinkSetButton/>
            <LinkUnsetButton/>
        </div>

        <editor-content :editor="editor" class="p-3 min-h-[300px] dark:text-gray-100"/>
        <!--        <div>{{ modelValue }}</div>-->
    </div>

</template>


<style lang="scss">
.ProseMirror {
    @apply p-3 focus-visible:rounded-md focus:border-0 focus-within:border-0 focus-visible:ring-1 focus-visible:ring-gray-500 focus-visible:ring-opacity-50;

    &:focus {
        outline: none;
        border: 0;
    }

    p {
        @apply mb-3;
    }

    ul, ol {
        @apply pl-5;
        li p {
            @apply mb-0.5;
        }
    }

    ul {
        @apply list-disc;
    }

    ol {
        @apply list-decimal;
    }

    strong {
        @apply font-bold
    }

    .syntax-highlight {
        color: red;
        font-style: italic;
    }

}
</style>
