<template>
  <AppLayout>
    <template #title>Assignees</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <TableSearch @click="Refresh"/>
          <v-data-table
            :headers="headers"
            density="comfortable"
            :items="filtered_assignees"
            :style="theme_table_style">
          </v-data-table>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";
import {useAuth0} from "@auth0/auth0-vue";

const store = GlobalStore();
const headers = [
  {title: 'ID',    value: 'id',              sortable: true},
  {title: 'Name',  value: 'assignee_company',sortable: true},
  {title: 'Class', value: 'assignee_class',  sortable: true},
  {title: 'City',  value: 'assignee_city',   sortable: true},
  {title: 'State', value: 'assignee_state',  sortable: true},
];
const {getAccessTokenSilently} = useAuth0();
const {ucc_assignees,table_search} = storeToRefs(store);
const filtered_assignees = computed(() => {
  const search_term = table_search.value.toLowerCase().trim();

  // If search is empty, return all items
  if (!search_term) return ucc_assignees.value;

  // Search across all specified properties
  return ucc_assignees.value.filter((assignee: any) => {
    return (
      assignee.id?.toString().toLowerCase().includes(search_term) ||
      assignee.assignee_company?.toString().toLowerCase().includes(search_term) ||
      assignee.assignee_class?.toString().toLowerCase().includes(search_term) ||
      assignee.assignee_city?.toString().toLowerCase().includes(search_term) ||
      assignee.assignee_state?.toString().toLowerCase().includes(search_term)
    );
  });
});
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
