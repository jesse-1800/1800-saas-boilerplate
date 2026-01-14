<template>
  <AppLayout>
    <template #title>UCC Filings</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <UccFilingsViewer :ucc_filing_id="ucc_filing_id" v-if="is_data_loaded && ucc_filing_id"/>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRoute} from "vue-router";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const route = useRoute<any>();
const ucc_filing_id = ref(null);
const {is_data_loaded} = storeToRefs(store);

onMounted(() => {
  ucc_filing_id.value = route.params.ucc_filing_id;
});
</script>
