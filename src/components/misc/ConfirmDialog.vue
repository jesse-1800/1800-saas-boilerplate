<template>
  <v-dialog persistent v-model="dialog.show" max-width="500">
    <v-card :style="theme_card_style">
      <v-card-title class="text-h6" v-html="dialog.title"/>
      <v-card-text v-html="dialog.content"/>
      <slot></slot>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="store.CancelDialog()">{{dialog.cancel_txt}}</v-btn>
        <v-btn color="red" variant="text" @click="store.ConfirmDialog()">{{dialog.ok_txt}}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import { GlobalStore } from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const {dialog} = storeToRefs(store);
</script>
