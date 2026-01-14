<template>
  <img
    class="mr-2 ml-2 pa-2 rounded-lg"
    height="60"
    :src="frontend_theme==='light'?`/assets/img/1800.svg`:'/assets/img/1800-white.png'"
    alt="1800 Office Solutions Logo">
</template>
 <script setup lang="ts">
 import {GlobalStore} from "@/stores/globals.ts";
 import {storeToRefs} from "pinia";

 const store = GlobalStore();
 const {frontend_theme} = storeToRefs(store);
 </script>
