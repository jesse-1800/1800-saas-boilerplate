<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <h1 class="my-5 font-weight-light text-center">
        <v-icon size="small">mdi-map-search</v-icon>
        Customers Interactive Map
      </h1>
      <BuyersInteractiveMap/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import BuyersInteractiveMap from "@/components/shared/buyers/BuyersInteractiveMap.vue";
</script>
