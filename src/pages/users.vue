
<template>
  <AppLayout>
    <template #title>Partners</template>
    <template #content>
      <!--Buttons-->
      <div class="d-flex align-center justify-space-between mb-7">
        <div>
          Users are fully managed in <a href="https://manage.auth0.com/dashboard/">Auth0 Dashboard</a>.
          This page allows you Add and Assign Users to groups (partners).
        </div>
        <div>
          <v-btn prepend-icon="mdi-refresh" class="mr-2" :loading="is_loading" :style="theme_btn_style" text="Refresh" @click="Refresh"/>
          <v-btn prepend-icon="mdi-plus-circle" :disabled="is_loading" :style="theme_btn_style" text="Add a user" @click="new_user_modal=true,edit_user=null"/>
        </div>
      </div>

      <!--Table-->
      <v-data-table :loading="is_loading" :style="theme_table_style" :items="auth0_users" :headers="headers" class="elevation-1 border">
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
              <v-select
                hide-details
                max-width="300"
                item-title="name"
                :items="idp_partners"
                density="compact"
                variant="outlined"
                :disabled="is_loading"
                v-model="selectedPartners[item.user_id]"
                @update:model-value="SetGroup($event,item.user_id)"
                item-value="id">
              </v-select>
            </td>
            <td>
              <v-switch
                @update:model-value="ToggleStatus(item)"
                :model-value="!item.blocked"
                :disabled="is_loading||IsMyself(item.user_id)"
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
                variant="outlined"
                text="Delete"
                :disabled="is_loading"
                prepend-icon="mdi-close"
                @click="DeleteUser(item.user_id)">
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <!--New user-->
      <NewUserModals :edit_user="edit_user" :is_owner="true"/>

      <!--Edit Profile-->
      <MyModal title="Edit Profile" v-model="edit_modal">
        <EditProfile :edit_user="edit_user"/>
      </MyModal>
    </template>
  </AppLayout>
</template>

<script setup>
  import {storeToRefs} from "pinia";
  import {useAuth0} from "@auth0/auth0-vue";
  import {GlobalStore} from "@/stores/globals";
  import {UccServer} from "@/plugins/ucc-server.ts";
  import {
    IsMyself,
    my_company_name,
    theme_btn_style,
    theme_table_style
  } from "@/composables/GlobalComposables";

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
    {title: 'Group',  sortable:false, value: 'group'},
    {title: 'Active', sortable:false, value: 'blocked'},
    {title: 'Manage', sortable:false, value: 'Manage'},
  ];
  const {getAccessTokenSilently} = useAuth0();
  const {FetchUsers,FetchPartners,FetchPartnerUsers} = store;
  const {auth0_users,idp_partners,idp_partner_users,new_user_modal} = storeToRefs(store);

  const SetGroup = async (partner_id, user_id) => {
    const token = await getAccessTokenSilently();
    const server = UccServer(token);
    const form = new FormData;

    form.append("partner_id", partner_id);
    form.append("user_id", user_id);

    server.post('/partnerusers/submit',form).then(res => {
      console.log(res.data);
      store.global_loading = true;
      store.FetchPartnerUsers(token).then(()=> {
        store.FetchAllData(token).then(() => {
          store.global_loading = false;
          store.ShowSuccess(`You switched to '${my_company_name.value}'`)
        });
      });
      FetchPartnerUsers(token);
    });
  };
  const EditUser = async (user_id) => {
    edit_user.value = auth0_users.value.find(u => u.user_id === user_id);
    edit_modal.value = true;
  }
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
    auth0_users.value.forEach(user => {
      const match = idp_partner_users.value.find(pu => pu.user_id === user.user_id)
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

  watch(() => auth0_users.value,() => {
    RebindPartnerData();
  },{immediate:true});

  onMounted(async () => {
    Refresh();
  });
</script>
