<template>
  <AppLayout>
    <template #title>({{ items_length }}) Customers</template>
    <template #content>
      <InnerLayout :sidebar="true">
        <template #sidebar>
          <v-card-text>
            <UccBuyersFilters/>
            <v-btn
              width="100%"
              prepend-icon="mdi-map-search-outline"
              class="d-flex ma-auto"
              :style="theme_btn_style"
              text="Open Interactive Map"
              @click="interactive_map=true">
            </v-btn>
          </v-card-text>
        </template>
        <template #content>
          <v-card :style="theme_card_style">
            <v-card-text>
              <v-data-table-server
                :headers="headers"
                :items="ucc_buyers"
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
                <!--<td>{{item.id}}</td>-->
                    <td>{{ item.buyer_company }}</td>
                    <td>{{ item.buyer_sic_desc }}</td>
                    <td>{{ item.buyer_city }}</td>
                    <td>{{ item.buyer_state }}</td>
                    <td>
                      <v-btn variant="outlined" size="small" prepend-icon="mdi-file-find" color="primary" @click="ViewBuyer(item.id)">View</v-btn>
                    </td>
                  </tr>
                </template>
              </v-data-table-server>
            </v-card-text>
          </v-card>
        </template>
      </InnerLayout>
      <UccBuyerViewer :buyer_id="view_buyer_id"/>
      <MyModal color="transparent" :fullscreen="true" title="Customer's Interactive Map" v-model="interactive_map">
        <v-app style="background:none">
          <v-main style="background:none">
            <BuyersInteractiveMap/>
          </v-main>
        </v-app>
      </MyModal>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {my_partner_id, theme_btn_style, ToggleModal} from "@/composables/GlobalComposables";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const headers = [
/*{ title: 'ID',       value: 'id',            sortable: true },*/
  { title: 'Company',  value: 'buyer_company', sortable: true },
  { title: 'Industry', value: 'buyer_sic_desc',sortable: true },
  { title: 'City',     value: 'buyer_city',    sortable: true },
  { title: 'State',    value: 'buyer_state',   sortable: true },
  { title: 'Manage',   value: 'manage',        sortable: false },
];
const interactive_map = ref(false);
const view_buyer_id = ref<any>(null);
const {ucc_buyers} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();
const {is_data_loaded,ucc_buyers_filters:filters} = storeToRefs(store);

// For server-based datatable
const curr_page      = ref<any>(1);
const sort_by        = ref(<any>[]);
const is_loading     = ref(false);
const items_length   = ref(0);
const items_per_page = ref<any>(25);
const sort_key       = computed(()=>(sort_by.value[0] ? sort_by.value[0].key:"id"));
const sort_order     = computed(()=>(sort_by.value[0] ? sort_by.value[0].order:"asc"));

const ViewBuyer = (buyer_id:string) => {
  view_buyer_id.value = buyer_id;
  ToggleModal('customer_profile',true);
}
const FetchRows = async() => {
  is_loading.value = true;
  const form = new FormData();
  const token = await getAccessTokenSilently();

  form.append("curr_page", curr_page.value);
  form.append("sort_by",   sort_key.value);
  form.append("order_by",  sort_order.value);
  form.append("page_size", items_per_page.value);

  // Filters
  form.append('search',    filters.value.search ?? '');
  form.append('industry',  filters.value.industry ?? '');
  form.append('city',      filters.value.city ?? '');
  form.append('state',     filters.value.state ?? '');

  UccServer(token).post(`/buyers/paginate/${my_partner_id.value}`,form).then(res=>{
    console.log(res.data);
    ucc_buyers.value = res.data.items;
    items_length.value = res.data.total;
  }).finally(()=>{
    is_loading.value = false;
  });
}

// sorting watcher
watch([curr_page,items_per_page,sort_by],FetchRows,{immediate:true});

// filters watcher
watch([()=>filters.value.state,()=>filters.value.city,()=>filters.value.industry],()=>{
  curr_page.value = 1;
  FetchRows();
});

// Watch for data to be fully loaded then do initial fetch
watch(is_data_loaded, () => {
  FetchRows();
});

// delay prevent xhr spamming
let search_timer = <any>null;
watch(()=>filters.value.search,()=>{
  clearTimeout(search_timer);
  search_timer = setTimeout(() => {
    curr_page.value = 1;
    FetchRows();
  }, 300);
});
</script>
