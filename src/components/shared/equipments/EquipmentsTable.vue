<template>
  <TableSearch @refresh="Refresh"/>
  <v-data-table
    :headers="headers"
    :items="filtered_equipments"
    density="comfortable"
    :style="theme_table_style">
  </v-data-table>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {theme_table_style} from "@/composables/GlobalComposables";
import {storeToRefs} from "pinia";

const headers = [
  { title: 'UCC ID',       value: 'ucc_filing_id',      sortable: true },
  { title: 'Unit',         value: 'equipment_unit',     sortable: true },
  { title: 'UCC Year',     value: 'equipment_ucc_year', sortable: true },
  { title: 'Number',       value: 'equipment_number',   sortable: true },
  { title: 'Brand',        value: 'equipment_brand',    sortable: true },
  { title: 'Model',        value: 'equipment_model',    sortable: true },
  { title: 'Description',  value: 'equipment_desc',     sortable: true },
  { title: 'Code',         value: 'equipment_code',     sortable: true },
  { title: 'Serial Number',value: 'equipment_serial_no',sortable: true },
  { title: 'Size',         value: 'equipment_size',     sortable: true },
  { title: 'End Year',     value: 'equipment_end_year', sortable: true },
  { title: 'Value',        value: 'equipment_value',    sortable: true },
];
const store = GlobalStore();
const {table_search} = storeToRefs(store);
const props = defineProps(['equipments']);
const {getAccessTokenSilently} = useAuth0();
const filtered_equipments = computed(() => {
  const search_term = table_search.value.toLowerCase().trim();

  // If search is empty, return all items
  if (!search_term) return props.equipments;

  // Search across all specified properties
  return props.equipments.filter((equipment: any) => {
    return (
      equipment.ucc_filing_id?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_unit?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_ucc_year?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_number?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_brand?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_model?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_desc?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_code?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_serial_no?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_size?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_end_year?.toString().toLowerCase().includes(search_term) ||
      equipment.equipment_value?.toString().toLowerCase().includes(search_term)
    );
  });
});
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
