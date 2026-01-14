<template>
  <AppLayout>
    <template #title>({{ items_length }}) Contacts</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <v-text-field
            width="300"
            variant="underlined"
            v-model="search_filter"
            placeholder="Search..."
            prepend-inner-icon="mdi-magnify"
            @click:appendInner="search_filter=''"
            :append-inner-icon="search_filter?'mdi-close':''">
          </v-text-field>

          <v-data-table-server
            :headers="headers"
            :items="ucc_contacts"
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
                <td>{{ item.fullname }}</td>
                <td>
                  <a href="javascript:;" @click="ViewBuyer(item.buyer_id)">
                    {{item.buyer_company}}
                  </a>
                </td>
                <td>{{ item.title.length ? item.title : '-' }}</td>
                <td>{{ item.email.length?item.email:'-' }}</td>
                <td>{{ FormatPhoneNumber(item.phone) }}</td>
                <td>
                  <v-btn
                    text="View"
                    size="small"
                    color="primary"
                    variant="outlined"
                    prepend-icon="mdi-file-find"
                    @click="ViewEditContact(item)">
                  </v-btn>
                </td>
              </tr>
            </template>
          </v-data-table-server>
        </v-card-text>
      </v-card>

      <MyModal color="transparent" :fullscreen="true" title="Customer's Interactive Map" v-model="interactive_map">
        <v-app style="background:none">
          <v-main style="background:none">
            <BuyersInteractiveMap/>
          </v-main>
        </v-app>
      </MyModal>

      <UccBuyerViewer :buyer_id="view_buyer_id"/>

      <ContactForm
        v-if="edit_contact"
        @onUpdate="FetchRows"
        :edit_contact="edit_contact"
        :buyer_id="edit_contact?.buyer_id">
      </ContactForm>

    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {ToggleModal} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import {theme_card_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const headers = [
  {title: 'ID',       value: 'id',      sortable: true},
  {title: 'Name',     value: 'fullname',sortable: true},
  {title: 'Company',  value: 'company', sortable: true},
  {title: 'Title',    value: 'title',   sortable: true},
  {title: 'Email',    value: 'email',   sortable: true},
  {title: 'Phone',    value: 'phone',   sortable: true},
  {title: 'Manage',   value: 'manage',  sortable: false},
];

const interactive_map = ref(false);
const edit_contact = ref<any>(null);
const search_filter = ref<string>("");
const view_buyer_id = ref<any>(null);
const {ucc_contacts,modals} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();

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
  console.log(buyer_id)
  ToggleModal('customer_profile',true);
}
const ViewEditContact = (contact:any) => {
  const contact_copy = {...contact};
  delete contact_copy.fullname;
  delete contact_copy.buyer_company;
  edit_contact.value = contact_copy;
  ToggleModal('contact_form',true);
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
  form.append('search', search_filter.value);
  UccServer(token).post(`/contacts/paginate/${my_partner_id.value}`,form).then(res=>{
    console.log(res.data);
    ucc_contacts.value = res.data.items;
    items_length.value = res.data.total;
  }).finally(()=>{
    is_loading.value = false;
  });
}
const FormatPhoneNumber = (phone_number:string) => {
  // Handle null, undefined, or non-string values
  if (phone_number == null) return '';

  // Convert to string if it's a number
  const phone_string = String(phone_number);

  // Remove all non-digit characters
  const digits = phone_string.replace(/\D/g, '');

  // Handle empty string after cleaning
  if (digits.length === 0) return '';

  // Handle 11 digits starting with 1 (US country code)
  if (digits.length === 11 && digits[0] === '1') {
    const cleaned = digits.slice(1);
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
  }

  // Handle exactly 10 digits
  if (digits.length === 10) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
  }

  // Return empty string for invalid formats
  return '';
}

// sorting watcher
watch([curr_page,items_per_page,sort_by],FetchRows,{immediate:true});

// delay prevent xhr spamming
let search_timer = <any>null;
watch(search_filter,()=>{
  clearTimeout(search_timer);
  search_timer = setTimeout(() => {
    curr_page.value = 1;
    FetchRows();
  }, 300);
});
</script>
