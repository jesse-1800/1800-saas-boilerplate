<template>
  <v-card :theme="backend_theme" :style="theme_card_style" @click="router.push(href)">
    <v-toolbar :style="theme_card_title_style" density="compact" class="text-center">
      <v-toolbar-title>{{title}}</v-toolbar-title>
    </v-toolbar>
    <v-card-text class="text-center">
      <v-img
        width="100"
        height="100"
        class="ma-auto"
        :src="image_url">
      </v-img>
    </v-card-text>
  </v-card>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useRouter} from "vue-router";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_card_title_style} from "@/composables/GlobalComposables";

const router = useRouter();
const store = GlobalStore();
const {backend_theme} = storeToRefs(store);
const props = defineProps(['title','icon','href']);

const image_url = computed(() => {
  if (backend_theme.value === 'light') {
    return `/assets/img/sidebar/dark/${props.icon}.png`;
  } else {
    return `/assets/img/sidebar/light/${props.icon}.png`;
  }
});
</script>
