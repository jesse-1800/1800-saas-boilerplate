<template>
  <v-card :style="{'--editor-height': props.height}" class="mt-5 border kanban-text-editor elevation-1 border-opacity-25">
    <div class="menu border-b" v-if="editor">
      <div v-if="$slots.label || label" class="pa-2 border-b">
        <slot v-if="$slots.label" name="label"/>
        <span v-else>{{label}}</span>
      </div>
      <div class="pa-1">
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-bold" @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-italic" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-strikethrough" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'is-active': editor.isActive('strike') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-code-tags" @click="editor.chain().focus().toggleCode().run()" :class="{ 'is-active': editor.isActive('code') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-eraser" @click="editor.chain().focus().unsetAllMarks().run()" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-paragraph" @click="editor.chain().focus().setParagraph().run()" :class="{ 'is-active': editor.isActive('paragraph') }" />
        <v-btn
          variant="text"
          size="x-small"
          icon="mdi-image"
          @click="PickImage"
        />

        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-1" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"/>
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-2" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"/>
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-3" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }"/>
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-4" @click="editor.chain().focus().toggleHeading({ level: 4 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 4 }) }"/>
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-5" @click="editor.chain().focus().toggleHeading({ level: 5 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 5 }) }"/>
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-header-6" @click="editor.chain().focus().toggleHeading({ level: 6 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 6 }) }"/>

        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-table" @click="editor.commands.insertTable({ rows: 3, cols: 3, withHeaderRow: true })" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-list-bulleted" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-list-numbered" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor.isActive('orderedList') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-code-braces" @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': editor.isActive('codeBlock') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-format-quote-close" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor.isActive('blockquote') }" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-minus" @click="editor.chain().focus().setHorizontalRule().run()" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-page-layout-header" @click="editor.chain().focus().setHardBreak().run()" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-undo" @click="editor.chain().focus().undo().run()" />
        <v-btn variant="text" elevation="0" size="x-small" icon="mdi-redo" @click="editor.chain().focus().redo().run()" />

        <slot name="header"/>
      </div>
    </div>
    <EditorContent :editor="editor" />
    <div v-if="editor && props.footer" class="footer">{{ editor.getHTML().length }} Characters</div>
  </v-card>
</template>


<script setup>
import Image from '@tiptap/extension-image';
import Table from '@tiptap/extension-table';
import StarterKit from '@tiptap/starter-kit';
import TableRow from '@tiptap/extension-table-row';
import TableCell from '@tiptap/extension-table-cell';
import {EditorContent,useEditor} from '@tiptap/vue-3';
import TableHeader from '@tiptap/extension-table-header';
import ImageResize from 'tiptap-extension-resize-image';

const props = defineProps({
  modelValue: String,
  footer: Boolean,
  label: String,
  insert: String,
  height: {
    type: String,
    default: '300px'
  }
});
const emit = defineEmits(['update:modelValue'])
const editor = useEditor({
  content: props.modelValue,
  extensions: [
    TableRow,
    TableCell,
    StarterKit,
    TableHeader,
    Table.configure({
      resizable: true
    }),
    Image.configure({
      inline: false,
      allowBase64: true,
    }),
    ImageResize.configure({
      inline: false,
    }),
  ],
  onUpdate({ editor }) {
    emit('update:modelValue', editor.getHTML())
  },
})
const PickImage = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = async () => {
    if (input.files && input.files[0]) {
      const file = input.files[0]
      const reader = new FileReader()
      reader.onload = () => {
        const base64 = reader.result
        editor.value.chain().focus().setImage({ src: base64 }).run()
      }
      reader.readAsDataURL(file)
    }
  }
  input.click()
}


// If modelValue changes externally, get the update
watch(() => props.modelValue, (newVal) => {
  if (editor.value && newVal !== editor.value.getHTML()) {
    editor.value.commands.setContent(newVal, false)
  }
})

// Watch for 'insert' prop to insert content at cursor
watch(() => props.insert, (newVal) => {
  if (editor.value && newVal) {
    editor.value.chain().focus().insertContent(newVal).run()
  }
});

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy()
  }
})
</script>

<style lang="scss">
.kanban-text-editor {
  width: 100%;
  .menu {
    padding-top: 1px;
    padding-bottom: 1px;
    text-align: center;
    height: auto;
  }
  .footer {
    background: #f1f1f1;
    padding: 3px 10px;
    text-align: right;
    font-size: 12px;
    border-top: 1px solid #d9d9d9;
  }
  .ProseMirror {
    padding: 20px;
    outline: none;
    min-height: var(--editor-height);
  }
  .ProseMirror:focus {
    outline: none;
  }
}
</style>
