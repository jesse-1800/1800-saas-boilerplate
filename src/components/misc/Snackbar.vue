<template>
  <v-snackbar v-model="snackbar.visible" :color="snackbar.type">
    <template v-if="snackbar.icon">
      <v-icon class="mr-2">{{ snackbar.icon }}</v-icon>
    </template>
    <span v-html="snackbar.content"></span>
    <template #actions>
      <v-btn icon="mdi-close" @click="CloseSnackbar"/>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import {storeToRefs} from 'pinia';
import {GlobalStore} from '@/stores/globals';

const store = GlobalStore();
const {snackbar} = storeToRefs(store);
const {CloseSnackbar} = store;
</script>
