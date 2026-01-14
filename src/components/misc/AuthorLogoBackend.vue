<template>
  <div v-if="color">
    <img
      class="mr-2 ml-2 pa-2 rounded-lg"
      height="60"
      :src="color==='light'?`/assets/img/1800-white.png`:'/assets/img/1800.svg'"
      alt="1800 Office Solutions Logo">
  </div>
  <div v-else>
    <img
      class="mr-2 ml-2 pa-2 rounded-lg"
      height="60"
      :src="backend_theme==='light'?`/assets/img/1800.svg`:'/assets/img/1800-white.png'"
      alt="1800 Office Solutions Logo">
  </div>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const props = defineProps(['color']);
const {backend_theme} = storeToRefs(store);
</script>
