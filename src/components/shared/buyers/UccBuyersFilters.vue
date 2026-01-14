<template>

  <FlexedBetween class="mb-5">
    <h3 class="font-weight-light">
      Filters
    </h3>
    <v-btn
      size="small"
      variant="text"
      class="d-flex"
      @click="ClearFilters"
      text="Clear Filters"
      prepend-icon="mdi-close">
    </v-btn>
  </FlexedBetween>

  <v-text-field
    density="compact"
    variant="outlined"
    placeholder="Search"
    v-model="filters.search"
    prepend-inner-icon="mdi-folder-search"
    @click:append-inner="filters.search=''"
    :append-inner-icon="filters.search? 'mdi-close':''">
  </v-text-field>

  <v-select
    label="City"
    density="compact"
    variant="outlined"
    placeholder="City"
    item-title="buyer_city"
    item-value="buyer_city"
    :return-object="false"
    :items="buyer_cities"
    v-model="filters.city"
    prepend-inner-icon="mdi-city"
    @click:append-inner="filters.city=null"
    :append-inner-icon="filters.city?'mdi-close':''">
  </v-select>

  <v-select
    label="State"
    density="compact"
    variant="outlined"
    placeholder="State"
    item-title="abbrev"
    item-value="abbrev"
    :return-object="false"
    :items="state_centers"
    v-model="filters.state"
    @click:append-inner="filters.state=null"
    prepend-inner-icon="mdi-map-marker-outline"
    :append-inner-icon="filters.state?'mdi-close':''">
  </v-select>

  <v-select
    label="Industry"
    density="compact"
    variant="outlined"
    placeholder="Industry"
    :return-object="false"
    :items="buyer_industries"
    v-model="filters.industry"
    item-title="buyer_sic_desc"
    item-value="buyer_sic_desc"
    prepend-inner-icon="mdi-domain"
    @click:append-inner="filters.industry=null"
    :append-inner-icon="filters.industry?'mdi-close':''">
  </v-select>

</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import type {UccBuyersFiltersType} from "@/types/StoreTypes";
import {state_centers} from "@/composables/GlobalComposables";

const store = GlobalStore();
const buyer_cities = ref([]);
const buyer_industries = ref([]);
const {getAccessTokenSilently} = useAuth0();
const {ucc_buyers_filters:filters} = storeToRefs(store);

const ClearFilters = () => {
  filters.value = <UccBuyersFiltersType>{
    search:  null,
    state:   null,
    industry:null,
  }
}
const FetchFilteringList = async() => {
  const token = await getAccessTokenSilently();
  UccServer(token).get('/buyers/filter-data').then(res => {
    buyer_industries.value = res.data.industries;
    buyer_cities.value = res.data.cities;
  });
}

onMounted(() => FetchFilteringList());
</script>
