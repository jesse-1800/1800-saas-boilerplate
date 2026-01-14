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
      <v-card class="frontend-modal global-radius outfit-font d-flex flex-column">
        <v-toolbar dark :color="color ? color : 'primary'" class="flex-shrink-0" style="position: sticky; top: 0; z-index: 1;">
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

        <v-card-text class="pa-4 overflow-y-auto" :style="`flex: 1 1 auto;background:${bg_color ?? 'transparent'}`">
          <slot/>
          <slot name="content"/>
        </v-card-text>

        <v-footer class="border-t rounded-0 pt-3 pb-3 flex-shrink-0" :color="footer_color" style="position: sticky; bottom: 0; z-index: 1;" v-if="$slots.footer">
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
