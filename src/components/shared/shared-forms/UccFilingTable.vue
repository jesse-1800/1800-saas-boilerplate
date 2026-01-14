<template>
  <template>
    <UccFilingsViewModal/>
  </template>
  <v-data-table
    :headers="headers"
    :items="ucc_filings"
    density="comfortable"
    :style="theme_table_style">
    <template #item.equipments="{item}:any">
      <td>
        <v-chip color="primary" :text="item.equipments.length"/>
      </td>
    </template>
    <template #item.manage="{item}">
      <td>
        <v-btn size="small" prepend-icon="mdi-folder-open" color="primary" @click="ViewUccFiling(item)" text="View"/>
      </td>
    </template>
  </v-data-table>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {ToggleModal} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const headers = [
  {title: "UCC ID",     value: "id",         sortable: true},
  {title: "UCC Date",   value: "ucc_date",   sortable: true},
  {title: "Equipments", value: "equipments", sortable: true},
  {title: "UCC Status", value: "ucc_status", sortable: true},
  {title: "Manage",     value: "manage",     sortable: false},
]
const {ucc_filings} = defineProps(['ucc_filings']);
const {view_ucc_id} = storeToRefs(GlobalStore());

const ViewUccFiling = (item:any) => {
  view_ucc_id.value = item.id;
  ToggleModal('ucc_filing_viewer',true);
}
</script>
