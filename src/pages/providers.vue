<template>
  <AppLayout>
    <template #title>Providers</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <TableSearch @click="Refresh"/>
          <v-data-table
            :headers="headers"
            density="comfortable"
            :style="theme_table_style"
            :items="filtered_providers">
          </v-data-table>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const headers = [
  {title: 'ID',    value: 'id',              sortable: true},
  {title: 'Name',  value: 'provider_company',sortable: true},
  {title: 'Class', value: 'provider_class',  sortable: true},
  {title: 'City',  value: 'provider_city',   sortable: true},
  {title: 'State', value: 'provider_state',  sortable: true},
];
const {getAccessTokenSilently} = useAuth0();
const {ucc_providers,table_search} = storeToRefs(store);
const filtered_providers = computed(() => {
  const search_term = table_search.value.toLowerCase().trim();

  // If search is empty, return all items
  if (!search_term) return ucc_providers.value;

  // Search across all specified properties
  return ucc_providers.value.filter((provider: any) => {
    return (
      provider.id?.toString().toLowerCase().includes(search_term) ||
      provider.provider_company?.toString().toLowerCase().includes(search_term) ||
      provider.provider_class?.toString().toLowerCase().includes(search_term) ||
      provider.provider_city?.toString().toLowerCase().includes(search_term) ||
      provider.provider_state?.toString().toLowerCase().includes(search_term)
    );
  });
});
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
