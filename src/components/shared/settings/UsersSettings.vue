<template>
  <v-expansion-panels :model-value="0" class="pb-10">
    <panel icon="mdi-account-multiple" class="border" title="User Accounts">
      <!--Table-->
      <v-data-table :loading="is_loading" :style="theme_table_style" :items="my_users" :headers="headers" class="elevation-1 border">
        <template #top>
          <!--Buttons-->
          <v-card-text class="d-flex border-b">
            <v-btn prepend-icon="mdi-refresh" class="mr-4" :loading="is_loading" :style="theme_btn_style" text="Refresh" @click="Refresh"/>
            <v-btn prepend-icon="mdi-plus-circle" :disabled="is_loading" :style="theme_btn_style" text="Add a user" @click="new_user_modal=true"/>
          </v-card-text>
        </template>
        <template v-slot:item="{item}">
          <tr>
            <!--<td>{{item.user_id}}</td>-->
            <td>
              <div class="d-flex align-center text-left">
                <img width="30" height="30" class="rounded-circle" :src="item.picture" alt="">
                <div class="ml-3">
                  {{item.name}}
                  <span v-if="IsMyself(item.user_id)">(You)</span>
                </div>
              </div>
            </td>
            <td>{{item.email}}</td>
            <td>{{item.role}}</td>
            <td>
              <v-switch
                :disabled="is_loading||IsMyself(item.user_id)"
                @update:model-value="ToggleStatus(item)"
                :model-value="!item.blocked"
                :loading="is_loading"
                density="compact"
                color="green"
                hide-details
                inset>
              </v-switch>
            </td>
            <td>
              <v-btn
                text="Edit"
                class="mr-1"
                size="small"
                color="primary"
                variant="outlined"
                :disabled="is_loading"
                prepend-icon="mdi-pencil"
                @click="EditUser(item.user_id)">
              </v-btn>
              <v-btn
                color="red"
                size="small"
                text="Delete"
                variant="outlined"
                :disabled="is_loading"
                prepend-icon="mdi-close"
                :style="theme_border_radius"
                @click="DeleteUser(item.user_id)">
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <!--New user-->
      <NewUserModals :is_owner="false"/>

      <!--Edit Profile-->
      <MyModal title="Edit Profile" v-model="edit_modal">
        <EditProfile :edit_user="edit_user"/>
      </MyModal>
    </panel>
  </v-expansion-panels>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server.ts";
import {
  IsMyself,
  my_profile,
  my_users,
  theme_border_radius,
  theme_btn_style,
  theme_table_style
} from "@/composables/GlobalComposables.js";

const store = GlobalStore();
const edit_user = ref(null);
const edit_modal = ref(false);
const is_loading = ref(false);
const selectedPartners = ref({});
const headers = [
  /*{title: 'User ID',sortable:true,  value: 'user_id'},*/
  {title: 'Name',   sortable:true,  value: 'name'},
  {title: 'Email',  sortable:true,  value: 'email'},
  {title: 'Role',  sortable:false,  value: 'role'},
  {title: 'Active', sortable:false, value: 'blocked'},
  {title: 'Manage', sortable:false, value: 'Manage'},
];
const {getAccessTokenSilently} = useAuth0();
const {FetchUsers,FetchPartners,FetchPartnerUsers} = store;
const {users,partner_users,new_user_modal} = storeToRefs(store);

const DeleteUser = async (user_id) => {
  const token = await getAccessTokenSilently();
  const is_confirmed = await store.OpenDialog("Confirm Action","Are you sure you want to delete this user?");
  if (is_confirmed) {
    is_loading.value = true;
    UccServer(token).post(`/users/destroy/${user_id}`).then(res => {
      console.log(res.data);
      store.ShowSuccess("User Deleted.");
      Refresh();
    }).finally(() => {
      is_loading.value = false;
    });
  }
}
const EditUser = async (user_id) => {
  edit_user.value = users.value.find(u => u.user_id === user_id);
  edit_modal.value = true;
}
const ToggleStatus = async (item) => {
  const token = await getAccessTokenSilently();
  const form = new FormData;
  is_loading.value = true;
  form.append('user_id', item.user_id);
  form.append('block_value', item.blocked ? 0 : 1);
  UccServer(token).post(`/users/toggle-status`,form).then(res => {
    is_loading.value = false;
    store.ShowSuccess("User status updated.");
    Refresh();
    console.log(res.data);
  });
}
const RebindPartnerData = () => {
  selectedPartners.value = {};
  // For preselecting the partners dropdown menu
  users.value.forEach(user => {
    const match = partner_users.value.find(pu => pu.user_id === user.user_id)
    if (match) selectedPartners.value[user.user_id] = Number(match.partner_id)
  })
}
const Refresh = async () => {
  is_loading.value = true;
  const token = await getAccessTokenSilently()
  await FetchUsers(token);
  await FetchPartners(token);
  await FetchPartnerUsers(token);
  is_loading.value = false;

  RebindPartnerData();
}

watch(()=>users.value,() => {
  RebindPartnerData();
},{ immediate:true });
</script>
