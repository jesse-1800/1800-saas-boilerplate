<template>
  <v-skeleton-loader
    :type="type"
    class="ma-0"
    :height="height"
    :width="width"
    color="transparent"
    :loading="global_loading">
    <slot></slot>
  </v-skeleton-loader>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {global_loading} = storeToRefs(store);

defineProps({
  height: null,
  width: null,
  type: {
    type: String,
    default: 'text'
  },
});

</script>
<style>
.v-skeleton-loader__text {
  margin-left: 0 !important
}
</style>
