<template>
  <MyModal
    max_width="1024"
    color="transparent"
    title="UCC File Viewer"
    :fullscreen="fullscreen"
    v-model="modals.ucc_filing_viewer">

    <template #append-toolbar-icon>
      <v-btn icon="mdi-open-in-new" :href="`/ucc-filing/${view_ucc_id}`"></v-btn>
      <v-btn icon="mdi-fullscreen" @click="fullscreen=!fullscreen"></v-btn>
    </template>
    <UccFilingsViewer/>
    <div class="mb-10"></div>

  </MyModal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const fullscreen = ref(false);
const {modals,view_ucc_id} = storeToRefs(store);
</script>
