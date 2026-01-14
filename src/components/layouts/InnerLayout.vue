<template>
  <div class="d-flex flex-column fill-height">

    <v-navigation-drawer
      prominent
      width="300"
      scrim="false"
      v-if="sidebar"
      location="left"
      :temporary="false"
      class="layout-sidebar"
      :style="theme_drawer_style">
      <slot name="sidebar"/>
    </v-navigation-drawer>

    <!-- Content -->
    <div class="flex-1 overflow-auto">
      <slot name="content"/>
    </div>

    <v-footer
      v-if="$slots.footer"
      class="layout-footer border-t">
      <slot name="footer"/>
    </v-footer>

  </div>
</template>

<script lang="ts" setup>
import {theme_drawer_style} from "@/composables/GlobalComposables";
const {sidebar} = defineProps({sidebar:Boolean})
</script>
