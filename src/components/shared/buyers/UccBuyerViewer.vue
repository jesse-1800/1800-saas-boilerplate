<template>
  <MyModal
    v-if="buyer"
    max_width="800"
    color="transparent"
    v-model="modals.customer_profile">
    <template #title>
      <Flexed-Between>
        <div>{{buyer?.buyer_company ?? 'Customer Profile'}}</div>
        <v-btn
          text="Enrich"
          @click="EnrichBuyer"
          prepend-icon="mdi-radar"
          :loading="is_loading"
          variant="elevated"
          color="primary">
        </v-btn>
      </Flexed-Between>
    </template>
    <v-expansion-panels :model-value="0" elevation="0">
      <panel icon="mdi-domain" :title="`Buyer Profile`">
        <v-card-text class="border">
          <BuyerForm :condensed="true" :buyer="buyer"/>
        </v-card-text>
      </panel>
      <panel icon="mdi-contacts" :title="`Contacts (${contacts.length})`">
        <template ></template>
        <ContactsTable class="border" :contacts="contacts" :buyer_id="buyer.id"/>
      </panel>
      <panel icon="mdi-text-box-multiple-outline" :title="`UCC Filings (${ucc_filings.length})`">
        <UccFilingTable :ucc_filings="ucc_filings"/>
      </panel>
    </v-expansion-panels>
  </MyModal>
</template>

<script lang="ts" setup>
import Axios from "axios";
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import type {UccBuyersType} from "@/types/StoreTypes";

const contacts = ref([]);
const ucc_filings = ref([]);
const is_loading = ref(false);
const buyer = ref<UccBuyersType|null>(null);

const {modals} = storeToRefs(GlobalStore());
const {getAccessTokenSilently} = useAuth0();
const {buyer_id} = defineProps(['buyer_id']);

const FindBuyerProfile = async () => {
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/buyers/buyer-profile/${buyer_id}`).then(res=>{
    buyer.value = res.data.buyer;
    contacts.value = res.data.contacts;
    ucc_filings.value = res.data.ucc_filings;
  });
}
const EnrichBuyer = async () => {
  is_loading.value = true;
  const form = new FormData;
  const new_contacts = [...contacts.value] as any;
  if (new_contacts.length == 1) {
    new_contacts.push(<any>{
      id: "",
      firstname: "",
      lastname: "",
    });
  }
  form.append('contacts',JSON.stringify(new_contacts));
  form.append('buyer_data',JSON.stringify(buyer.value));
  Axios.post(import.meta.env.VITE_PABBLY_WEBHOOK,form).then(res => {
    console.log(res.data);
  }).finally(() => {
    setTimeout(() => {
      is_loading.value = false;
      FindBuyerProfile();
    },10000)
  });
}

watch(()=>buyer_id,(new_buyer_id) => {
  if (new_buyer_id) FindBuyerProfile();
});
</script>
