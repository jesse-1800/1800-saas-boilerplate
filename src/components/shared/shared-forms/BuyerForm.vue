<template>
  <v-row v-if="buyer">
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Company" v-model="buyer.buyer_company"/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Website" v-model="buyer.buyer_website"/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Address" v-model="buyer.buyer_adress1"/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="City" v-model="buyer.buyer_city"/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="State" v-model="buyer.buyer_state"/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="ZIP" v-model="buyer.buyer_zip"/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Phone" v-model="buyer.buyer_phone"/>
    </v-col>
  </v-row>

  <div class="text-center mb-2 mt-5">
    <v-btn @click="UpdateBuyer" prepend-icon="mdi-floppy" color="primary">Save Changes</v-btn>
  </div>
</template>

<script setup>
import {useAuth0} from "@auth0/auth0-vue";
import {UccServer} from "@/plugins/ucc-server";
import {GlobalStore} from "@/stores/globals.js";

const store = GlobalStore();
const {getAccessTokenSilently} = useAuth0();
const {buyer} = defineProps(['buyer','condensed']);

const UpdateBuyer = async() => {
  const form = new FormData;
  const token = await getAccessTokenSilently();
  form.append('buyer', JSON.stringify(buyer));
  UccServer(token).post('/buyers/update',form).then(res => {
    console.log(res.data);
    store.ShowSuccess('Customer profile updated successfully');
  });
}
</script>
