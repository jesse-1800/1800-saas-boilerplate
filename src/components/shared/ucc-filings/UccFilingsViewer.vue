<template>
  <!-- UCC Filing (always visible) -->
  <v-tabs v-model="active_tab" grow>
    <v-tab value="ucc-filing">UCC Filing</v-tab>
    <v-tab value="equipments">Equipments</v-tab>
    <v-tab value="buyer">Customer</v-tab>
    <v-tab value="provider">Service Provider</v-tab>
    <v-tab value="assignee">Assignee</v-tab>
    <v-tab value="contacts">Contacts</v-tab>
  </v-tabs>

  <template v-if="ucc_filing">
    <v-window v-model="active_tab">
      <v-window-item value="ucc-filing">
        <v-card>
          <v-card-text>
            <UccFilingForm :ucc_filing="ucc_filing"/>
          </v-card-text>
        </v-card>
      </v-window-item>
      <v-window-item value="equipments">
        <v-card>
          <v-card-text>
            <EquipmentsTable :equipments="equipments"/>
          </v-card-text>
        </v-card>
      </v-window-item>
      <v-window-item value="buyer">
        <v-card class="pb-10">
          <v-card-text>
            <BuyerForm :buyer="buyer"/>
          </v-card-text>
        </v-card>
      </v-window-item>
      <v-window-item value="provider">
        <v-card class="pb-10">
          <v-card-text v-if="provider">
            <v-text-field variant="underlined" label="Provider Company" :model-value="provider.provider_company" readonly/>
            <v-text-field
              variant="underlined"
              label="Location"
              hide-details
              :model-value="`${provider.provider_city}, ${provider.provider_state}`"
              readonly
            />
          </v-card-text>
          <v-card-text v-else>
            <h2 class="my-10 text-center font-weight-light">No Service Provider found</h2>
          </v-card-text>
        </v-card>
      </v-window-item>
      <v-window-item value="assignee">
        <v-card class="pb-10">
          <v-card-text v-if="assignee">
            <v-text-field variant="underlined" label="Assignee Company" :model-value="assignee.assignee_company" readonly/>
            <v-text-field
              variant="underlined"
              label="Location"
              hide-details
              :model-value="`${assignee.assignee_city}, ${assignee.assignee_state}`"
              readonly
            />
          </v-card-text>
          <v-card-text v-else>
            <h2 class="my-10 text-center font-weight-light">No assignee found</h2>
          </v-card-text>
        </v-card>
      </v-window-item>
      <v-window-item value="contacts">
        <v-card>
          <v-card-text>
            <ContactsTable :contacts="contacts" :buyer_id="buyer?.id"/>
          </v-card-text>
        </v-card>
      </v-window-item>
    </v-window>
  </template>
  <template v-else>
    <div class="text-center my-16">
      <v-progress-circular size="100" indeterminate/>
      <p class="mt-3">Loading data, please wait...</p>
    </div>
  </template>

  <ContactForm @onUpdate="FetchUccData" :buyer_id="buyer?.id" :edit_contact="edit_contact"/>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {theme_table_style} from "@/composables/GlobalComposables";
import EquipmentsTable from "@/components/shared/equipments/EquipmentsTable";
import BuyerForm from "@/components/shared/shared-forms/BuyerForm.vue";
import UccFilingForm from "@/components/shared/shared-forms/UccFilingForm.vue";

const store = GlobalStore();
const is_loading = ref(false);
const active_tab = ref("ucc-filing")

const ucc_filing = ref(null);
const buyer      = ref(null);
const provider   = ref(null);
const assignee   = ref(null);
const contacts   = ref([]);
const equipments = ref([]);

const edit_contact = ref(null);
const {modals,view_ucc_id} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();

const FetchUccData = async() => {
  is_loading.value = true;
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/data/find_ucc_data/${view_ucc_id.value}`).then(res=>{
    console.log(res.data);
    ucc_filing.value = res.data.ucc_filing;
    buyer.value      = res.data.buyer;
    contacts.value   = res.data.contacts;
    provider.value   = res.data.provider;
    assignee.value   = res.data.assignee;
    equipments.value = res.data.equipments;
  }).finally(()=>{
    is_loading.value = false;
  });
}

watch(()=>view_ucc_id.value,(v)=> {
  if (v) FetchUccData();
},{immediate:true});
</script>
