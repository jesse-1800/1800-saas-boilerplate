<template>
  <flexed-between class="mb-5">
    <div>
      <v-text-field
        width="250"
        hide-details
        density="compact"
        variant="outlined"
        placeholder="Search..."
        v-model="table_search"
        prepend-inner-icon="mdi-magnify"
        @click:append-inner="table_search=''"
        :append-inner-icon="table_search?'mdi-close':''">
      </v-text-field>
    </div>
    <v-spacer/>
    <v-btn
      text="Refresh"
      color="primary"
      @click="$emit('refresh')"
      prepend-icon="mdi-refresh">
    </v-btn>
    <slot name="append-right"></slot>
  </flexed-between>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {table_search} = storeToRefs(store);

onUnmounted(() => {
  table_search.value = "";
});
</script>
