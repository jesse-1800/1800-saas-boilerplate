<template>
  <AppLayout>
    <template #title>({{items_length}}) UCC Filings</template>
    <template #content>
      <InnerLayout :sidebar="true">
        <template #sidebar>
          <v-card-text>
            <UccFilingsFilters/>
          </v-card-text>
        </template>
        <template #content>
          <v-card :style="theme_card_style">
            <v-card-text>
              <v-data-table-server
                :headers="headers"
                :items="ucc_filings"
                density="comfortable"
                :loading="is_loading"
                v-model:page="curr_page"
                v-model:sort-by="sort_by"
                :style="theme_table_style"
                :items-length="items_length"
                :items-per-page-options="[25,50,100]"
                v-model:items-per-page="items_per_page">
                <template #item="{item}">
                  <tr>
                    <td>{{item.id}}</td>
                    <td>{{ item.buyer_company }}</td>
                    <td>{{ item.buyer_state }}</td>
                    <td><v-chip color="primary">{{item.equipment_count}}</v-chip></td>
                    <td>{{item.ucc_date}}</td>
                    <td>{{item.ucc_status}}</td>
                    <td>
                      <v-btn variant="outlined" size="small" prepend-icon="mdi-file-find" color="primary" @click="ViewUcc(item.id)">View</v-btn>
                    </td>
                  </tr>
                </template>
              </v-data-table-server>
            </v-card-text>
          </v-card>
        </template>
      </InnerLayout>

      <UccFilingsViewModal :ucc_filing_id="view_ucc_id"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import moment from "moment";
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {my_partner_id, ToggleModal} from "@/composables/GlobalComposables";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const headers = <any>[
  {title: "UCC ID",     value: "id",              sortable: true},
  {title: "Company",    value: "buyer_company",   sortable: true},
  {title: "State",      value: "buyer_state",     sortable: true},
  {title: "Equipments", value: "equipment_count", sortable: true},
  {title: "UCC Date",   value: "ucc_date",        sortable: true},
  {title: "UCC Status", value: "ucc_status",      sortable: true},
  {title: "Manage",     value: "manage",          sortable: false},
]
const {ucc_filings} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();
const {ucc_filing_filters:filters,view_ucc_id,is_data_loaded} = storeToRefs(store);

// For server-based datatable
const curr_page      = ref<any>(1);
const sort_by        = ref(<any>[]);
const is_loading     = ref(false);
const items_length   = ref(0);
const items_per_page = ref<any>(25);
const sort_key       = computed(()=>(sort_by.value[0] ? sort_by.value[0].key:"id"));
const sort_order     = computed(()=>(sort_by.value[0] ? sort_by.value[0].order:"asc"));

const ViewUcc = (ucc_filing_id:string) => {
  view_ucc_id.value = ucc_filing_id;
  ToggleModal('ucc_filing_viewer',true);
}
const FetchRows = async() => {
  is_loading.value = true;
  const form = new FormData();
  const token = await getAccessTokenSilently();

  form.append("curr_page",    curr_page.value);
  form.append("sort_by",      sort_key.value);
  form.append("order_by",     sort_order.value);
  form.append("page_size",    items_per_page.value);

  form.append('search',       filters.value.search     ?? '');
  form.append('start_date',   filters.value.start_date ? moment(filters.value.start_date).format("MM/DD/YYYY"):"");
  form.append('end_date',     filters.value.end_date   ? moment(filters.value.end_date).format("MM/DD/YYYY"):"");
  form.append('provider_id',  filters.value.provider_id   ?? '');
  form.append('assignee_id',  filters.value.assignee_id   ?? '');
  form.append('ucc_status',   filters.value.ucc_status    ?? '');
  form.append('buyer_state',  filters.value.buyer_state   ?? '');
  form.append('equipment_min',filters.value.equipment_min ?? '');
  form.append('equipment_max',filters.value.equipment_max ?? '');

  UccServer(token).post(`/uccfilings/paginate/${my_partner_id.value}`,form).then(res=>{
    ucc_filings.value = res.data.items;
    items_length.value = res.data.total;
  }).finally(()=>{
    is_loading.value = false;
  });
}

// sorting watcher
watch([curr_page,items_per_page,sort_by],FetchRows,{immediate:true});

// Watch for data to be fully loaded then do initial fetch
watch(is_data_loaded, () => {
  FetchRows();
});


// filters watcher
watch(
  [
    () => filters.value.provider_id,
    () => filters.value.assignee_id,
    () => filters.value.ucc_status,
    () => filters.value.start_date,
    () => filters.value.end_date,
    () => filters.value.buyer_state,
    () => filters.value.equipment_min,
    () => filters.value.equipment_max,
  ],
  () => {
    curr_page.value = 1;
    FetchRows();
  }
);

// separate search watcher to prevent
// spamming xhr requests
let search_timer = <any>null;
watch(
  () => filters.value.search,
  () => {
    clearTimeout(search_timer);
    search_timer = setTimeout(() => {
      curr_page.value = 1;
      FetchRows();
    }, 300);
  }
);
</script>
