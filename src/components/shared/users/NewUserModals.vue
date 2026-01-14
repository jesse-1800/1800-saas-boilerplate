<template>
  <!--The User Form-->
  <MyModal v-model="new_user_modal" max_width="500" title="Add a user" color="transparent">
    <v-select
      label="Role"
      item-value="id"
      item-title="name"
      variant="outlined"
      :items="custom_roles"
      v-model="user_info.role_id">
    </v-select>
    <v-row>
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field variant="outlined" v-model="user_info.firstname" label="First name"/>
      </v-col>
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field variant="outlined" v-model="user_info.lastname" label="Last name"/>
      </v-col>
    </v-row>
    <v-text-field variant="outlined" v-model="user_info.username" label="Username/Email" type="email"/>
    <v-text-field variant="outlined" v-model="user_info.password" label="Password" :type="show_password ? 'password':'text'">
      <template #append-inner>
        <v-icon @click="show_password=!show_password" v-if="show_password" icon="mdi-eye-circle-outline"/>
        <v-icon @click="show_password=!show_password" v-else icon="mdi-eye-closed"/>
      </template>
    </v-text-field>
    <template #footer>
      <v-btn
        text="Submit"
        @click="SubmitUser"
        :loading="is_loading"
        :style="theme_btn_style"
        append-icon="mdi-play">
      </v-btn>
    </template>
  </MyModal>

  <!--Shown when user is added-->
  <MyModal v-model="user_added_modal" max_width="500" title="User successfully added!" color="transparent">
    <v-text-field readonly variant="outlined" v-model="user_info.username" label="Username/Email" type="email">
      <template #append-inner>
        <v-icon @click="CopyUsername" icon="mdi-content-copy"/>
      </template>
    </v-text-field>
    <v-text-field readonly variant="outlined" v-model="user_info.password" label="Password" type="password">
      <template #append-inner>
        <v-icon @click="CopyPassword" icon="mdi-content-copy"/>
      </template>
    </v-text-field>
    <template #footer>
      <v-btn :style="theme_btn_style" @click="CleanForm" text="Done"/>
    </template>
  </MyModal>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {Auth0UserType} from "@/types/StoreTypes";
import {UccServer} from "@/plugins/ucc-server.ts";
import {my_partner_id} from "@/composables/GlobalComposables";
import {theme_btn_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const is_loading = ref(false);
const show_password = ref(false);
const custom_roles = computed(() => {
  if (is_owner) {
    return auth0_roles.value;
  } else {
    return auth0_roles.value.filter(r => r.name !== 'Admin');
  }
});
const {getAccessTokenSilently} = useAuth0();
const {is_owner} = defineProps(['is_owner']);
const user_info = ref<Auth0UserType>({
  user_id: '',
  firstname: '',
  lastname: '',
  username: '',
  password: '',
  role_id: 'rol_dIGESJHkxkRFCQW4',
});
const {new_user_modal,user_added_modal,auth0_roles} = storeToRefs(store);

const CleanForm = () => {
  user_added_modal.value = false;
  user_info.value = {
    user_id:   '',
    firstname: '',
    lastname:  '',
    username:  '',
    password:  '',
    role_id: 'rol_dIGESJHkxkRFCQW4',
  }
}
const SubmitUser = async () => {
  const token = await getAccessTokenSilently();
  const form = new FormData;
  is_loading.value = true;
  form.append('partner_id', my_partner_id.value);
  form.append('form', JSON.stringify(user_info.value));
  UccServer(token).post('/users/store',form).then(res => {
    console.log(res.data);
    store.ShowSuccess(res.data.message);
    store.FetchUsers(token);
    store.FetchPartnerUsers(token);
    new_user_modal.value = false;
    user_added_modal.value = true;
  }).finally(() => {
    is_loading.value = false;
  });
}
const CopyUsername = async() => {
  if (!user_info.value.username) return
  await navigator.clipboard.writeText(user_info.value.username)
  store.ShowSuccess('Username copied')
}
const CopyPassword = async() => {
  if (!user_info.value.password) return
  await navigator.clipboard.writeText(user_info.value.password)
  store.ShowSuccess('Password copied')
}

onMounted(() => {
  CleanForm();
});
</script>
