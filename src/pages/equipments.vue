<template>
  <AppLayout>
    <template #title>All Equipments</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <EquipmentsTable :equipments="ucc_equipments"/>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const {ucc_equipments} = storeToRefs(store);
</script>
