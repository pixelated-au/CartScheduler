import { Extension } from '@tiptap/core'
import { Plugin } from 'prosemirror-state'
import { Decoration, DecorationSet } from 'prosemirror-view'

export const SyntaxHighlight = Extension.create({
    name: 'syntaxHighlight',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                props: {
                    decorations(state) {
                        const { doc } = state
                        const decorations = []
                        const regex = /\{\{\s*[\w\d_]+\s*\}\}/g

                        doc.descendants((node, pos) => {
                            if (!node.isText) return

                            const text = node.text
                            let match

                            while ((match = regex.exec(text)) !== null) {
                                const start = pos + match.index
                                const end = start + match[0].length

                                decorations.push(
                                    Decoration.inline(start, end, {
                                        class: 'syntax-highlight',
                                    })
                                )
                            }
                        })

                        return DecorationSet.create(doc, decorations)
                    },
                },
            }),
        ]
    },
})



