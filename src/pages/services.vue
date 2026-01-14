<template>
  <Layout>
    <template #title>Services</template>

    <template #content>

      <v-card>
        <v-card-text>
          <h1>List of Products</h1>
          <v-list>
            <v-list-item v-for="item in products">
              {{item}}
            </v-list-item>
          </v-list>
        </v-card-text>
      </v-card>
    </template>
  </Layout>
</template>

<script lang="ts" setup>
import Layout from "@/layouts/Layout.vue";
import axios from "axios";

const products = ref([]);

onMounted(() => {
  axios.get("/api/fetch_products").then(response => {
    products.value = response.data;
  });
});
</script>
