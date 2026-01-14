<template>
  <v-btn @click="store.SetState({backend_theme:'dark'})" v-tooltip="`Switch to Dark Theme`" icon="mdi-lightbulb-on-outline"  v-if="backend_theme==='light'"/>
  <v-btn @click="store.SetState({backend_theme:'light'})" v-tooltip="`Switch to Light Theme`" icon="mdi-brightness-4" v-else></v-btn>
</template>

<script setup>
import {GlobalStore} from "@/stores/globals";
import {storeToRefs} from "pinia";

const store = GlobalStore();
const {backend_theme} = storeToRefs(store);
</script>
