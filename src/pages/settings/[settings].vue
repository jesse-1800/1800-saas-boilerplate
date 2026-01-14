<template>
  <AppLayout>
    <template #title>Settings</template>
    <template #content>
      <v-tabs v-model="active_tab" dark height="48">

        <!--Tab headers-->
        <v-tab
          text="My Profile"
          to="/settings/profile"
          prepend-icon="mdi-account-circle-outline">
        </v-tab>
        <v-tab
          text="Business Profile"
          to="/settings/business"
          prepend-icon="mdi-briefcase">
        </v-tab>
        <v-tab
          text="User Accounts"
          to="/settings/users"
          v-if="is_manager || is_admin"
          prepend-icon="mdi-account-multiple">
        </v-tab>
        <v-tab
          text="Proposal Settings"
          to="/settings/proposal"
          prepend-icon="mdi-file-document-edit">
        </v-tab>
        <v-tab
          text="Custom Fields"
          to="/settings/custom-fields"
          prepend-icon="mdi-form-textbox">
        </v-tab>
        <v-tab
          text="Addons"
          to="/settings/addons"
          prepend-icon="mdi-puzzle">
        </v-tab>
      </v-tabs>

      <!-- Tab content -->
      <v-window v-model="active_tab" class="mt-4">
        <v-window-item value="profile">
          <v-card flat>
            <EditProfile/>
          </v-card>
        </v-window-item>
        <v-window-item value="business">
          <v-card flat>
            <BusinessSettings @submit="SubmitForm" :is_modal="false"/>
            <BrandSettings @submit="SubmitForm" :is_modal="false"/>
            <MailSettings @submit="SubmitForm" :is_modal="false" class="mt-5"/>
          </v-card>
        </v-window-item>
        <v-window-item value="users">
          <v-card flat>
            <UsersSettings/>
          </v-card>
        </v-window-item>
        <v-window-item value="proposal">
          <v-card flat class="pb-15">
            <PropDefaults/>
          </v-card>
        </v-window-item>
        <v-window-item value="custom-fields">
          <v-card flat class="pb-15">
            <CustomFields/>
          </v-card>
        </v-window-item>
        <v-window-item value="addons">
          <v-card flat class="pb-15">
            <Addons/>
          </v-card>
        </v-window-item>
      </v-window>

    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRoute} from "vue-router";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {
  is_admin,
  is_manager,
  my_user_id,
  my_partner_object
} from "@/composables/GlobalComposables";

const store = GlobalStore();
const route = <any>useRoute();
const active_tab = ref('home');
const {partner_form} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();
const SubmitForm = async () => {
  const token = await getAccessTokenSilently();
  store.SubmitPartnerForm(token,my_user_id.value);
};

onMounted(() => {
  // Wait for partner object to be available
  watch(() => my_partner_object.value,(new_value:any)=>{
    partner_form.value = {...new_value};
  },{immediate:true});
});

watch(()=>route.params.settings, (new_page) => {
  active_tab.value = new_page;
})
</script>
