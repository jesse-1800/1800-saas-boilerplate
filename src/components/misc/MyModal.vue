<template>
  <v-row justify="center">
    <v-dialog
      persistent
      v-model="dialog"
      :max-width="max_width"
      scrim="rgba(0,0,0,0.9)"
      :fullscreen="fullscreen"
      :max-height="max_height"
      transition="dialog-bottom-transition">
      <v-card :style="modal_style" class="global-radius outfit-font d-flex flex-column">
        <v-toolbar :density="density" v-if="show_toolbar" dark :color="color ? color : 'primary'" class="flex-shrink-0 border-b" style="position: sticky; top: 0; z-index: 1;">
          <template v-if="$slots.toolbar">
            <slot name="toolbar"/>
          </template>
          <template v-else>
            <v-toolbar-title>
              <span v-if="$slots.title"><slot name="title"/></span>
              <span v-else>{{title}}</span>
            </v-toolbar-title>

            <slot name="append-toolbar-icon"/>
            <v-btn v-if="!hide_close_btn" icon='mdi-close' dark @click="CloseDialog"/>
          </template>
        </v-toolbar>

        <v-card-text
          class="pa-0"
          v-if="no_scroll"
          :style="`flex: 1 1 auto;background:${bg_color ?? 'transparent'}`">
          <slot/>
          <slot name="content"/>
        </v-card-text>
        <v-card-text
          v-else
          :class="`pa-${card_padding} overflow-y-auto`"
          :style="`flex: 1 1 auto;background:${bg_color ?? 'transparent'}`">
          <slot/>
          <slot name="content"/>
        </v-card-text>

        <v-footer :style="theme_table_style" class="border-t rounded-0 pt-3 pb-3 flex-shrink-0" :color="footer_color" style="position: sticky; bottom: 0; z-index: 1;" v-if="$slots.footer">
          <slot name="footer"/>
        </v-footer>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script setup>
import {
  theme_border_radius,
  theme_modal_style,
  theme_table_style} from "@/composables/GlobalComposables";

const props = defineProps({
  modelValue: Boolean,
  title: String,
  fullscreen: Boolean,
  max_width: [String, Number],
  max_height: [String, Number],
  color: String,
  footer_color: String,
  bg_color: String,
  density:  {
    type: String,
    default: 'comfortable',
  },
  no_scroll: {
    type: Boolean,
    default: false,
  },
  card_padding: {
    type: Number,
    default: 4,
  },
  show_toolbar: {
    type: Boolean,
    default: true,
  },
  hide_close_btn: {
    type: Boolean,
    default: false
  },
});
const emit = defineEmits(['update:modelValue', 'close', 'onmount']);
const dialog = ref(props.modelValue);
const modal_style = computed(() => {
  if (props.fullscreen === true) {
    return (`
      ${theme_modal_style.value}
    `);
  } else {
    return (`
      ${theme_modal_style.value};
      ${theme_border_radius.value}
    `);
  }
})

const CloseDialog = () => {
  dialog.value = false;
}

onMounted(() => {
  dialog.value = props.modelValue;
  emit('onmount');
});
watch(() => props.modelValue, (newValue) => {
  dialog.value = newValue;
});
watch(dialog, (val) => {
  emit('update:modelValue', val);
  if (!val) emit('close');
});
</script>
