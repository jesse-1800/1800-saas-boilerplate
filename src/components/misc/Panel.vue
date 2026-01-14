<template>
  <v-expansion-panel :value="value" :style="theme_table_style">
    <v-expansion-panel-title :style="theme_panel_color">
      <slot name="icon"/>
      <v-icon v-if="icon" :icon="icon"/>
      <template v-if="$slots.title">
        <slot name="title"/>
      </template>
      <template v-else>
        <span>&nbsp; {{ title }}</span>
      </template>
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      <slot></slot>
      <v-footer style="background:transparent" class="border-t" v-if="$slots.footer">
        <slot name="footer"/>
      </v-footer>
    </v-expansion-panel-text>
  </v-expansion-panel>
</template>

<script lang="ts" setup>
import {theme_panel_color, theme_table_style} from "@/composables/GlobalComposables";
defineProps(['title', 'icon', 'color','value'])
</script>
<style scoped>
::v-deep(.v-expansion-panel-text__wrapper) {
  padding: 0 !important;
}
</style>
