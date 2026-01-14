<template>
  <v-row>
    <!--Left Column-->
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-card :style="theme_card_style">
        <v-card-text>
          <strong class="mb-5 d-block">Personal Data</strong>
          <!--Avatar-->
          <div class="d-flex align-center pa-5 border border-opacity-25">
            <div class="logo-container mr-5">
              <v-img
                width="165"
                height="165"
                class="rounded"
                :src="editable.picture">
              </v-img>
            </div>
            <div>
              <p>Your Profile Image</p>
              <p>The proposed size is 512 •512 no bigger than 2MB</p>
              <v-file-input
                hide-details
                class="mt-5"
                density="compact"
                variant="outlined"
                v-model="file_input"
                label="Click to upload"
                @update:modelValue="TempAvatar">
              </v-file-input>
            </div>
          </div>

          <!--Personal Info-->
          <div class="mt-15">
            <v-text-field v-model="editable.firstname" density="comfortable" variant="outlined" label="First name"/>
            <v-text-field v-model="editable.lastname" density="comfortable" variant="outlined" label="Last name"/>
            <v-text-field v-model="editable.email" density="comfortable" variant="outlined" label="Email"/>
          </div>
        </v-card-text>
        <v-card-text class="border-t">
          <v-btn
            text="Update Profile"
            :style="theme_btn_style"
            class="ma-auto d-flex"
            @click="UpdateProfile('profile')"
            :loading="is_loading=='profile'">
          </v-btn>
        </v-card-text>
      </v-card>
    </v-col>

    <!--Right Column-->
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-card :style="theme_card_style">
        <v-card-text>
          <strong class="mb-5 d-block">Change Password</strong>
          <v-text-field
            variant="outlined"
            label="New Password"
            density="comfortable"
            v-model="editable.new_password"
            :type="show_newpass?'text':'password'">
            <template #append-inner>
              <v-icon v-tooltip="`Hide Password`" @click="show_newpass=false" v-if="show_newpass">mdi-eye-closed</v-icon>
              <v-icon v-tooltip="`Show Password`" @click="show_newpass=true" v-else>mdi-eye</v-icon>
            </template>
          </v-text-field>
          <v-text-field
            hide-details
            variant="outlined"
            density="comfortable"
            label="Confirm Password"
            v-model="editable.confirm_password"
            :type="show_confirm?'text':'password'">
            <template #append-inner>
              <v-icon v-tooltip="`Hide Password`" @click="show_confirm=false" v-if="show_confirm">mdi-eye-closed</v-icon>
              <v-icon v-tooltip="`Show Password`" @click="show_confirm=true" v-else>mdi-eye</v-icon>
            </template>
          </v-text-field>
        </v-card-text>
        <v-card-text class="border-t">
          <v-btn
            text="Update Password"
            :style="theme_btn_style"
            class="ma-auto d-flex"
            :disabled="!password_valid"
            @click="UpdateProfile('password')"
            :loading="is_loading=='password'">
          </v-btn>
        </v-card-text>
      </v-card>

      <!--Email signature-->
      <v-card :style="theme_card_style" class="mt-5">
        <v-card-text>
          <strong class="mb-5 d-block">Email Signature</strong>
          <text-editor :height="'120px'" v-model="editable.email_signature"/>
        </v-card-text>
        <v-card-text class="border-t">
          <v-btn
            text="Update Signature"
            :style="theme_btn_style"
            @click="UpdateSignature"
            class="ma-auto d-flex"
            :loading="is_loading=='signature'">
          </v-btn>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server.ts";
import {FindPartnerUser, my_user_id, ParseValidName} from "@/composables/GlobalComposables";
import {theme_btn_style} from "@/composables/GlobalComposables";
import {theme_card_style} from "@/composables/GlobalComposables";
import {my_partner_user_profile} from "@/composables/GlobalComposables";

const store = GlobalStore();
const show_newpass = ref(false);
const show_confirm = ref(false);
const file_input = ref<any>(null);
const is_loading = ref<any>(null);
const props = defineProps(['edit_user']);
const editable = ref<any>({
  user_id:         "",
  firstname:       "",
  lastname:        "",
  email:           "",
  picture:         "",
  new_password:    "",
  confirm_password:"",
  email_signature: "",
});
const password_valid = computed(() => {
  const password = editable.value.new_password;
  const confirm = editable.value.confirm_password;
  if (!password || !confirm) return false
  if (password !== confirm) return false

  // Auth0 Fair policy: 8+ chars, 3 of 4 character types
  if (password.length < 8) return false

  let criteria_met = 0
  if (/[a-z]/.test(password)) criteria_met++
  if (/[A-Z]/.test(password)) criteria_met++
  if (/\d/.test(password)) criteria_met++
  if (/[^A-Za-z\d]/.test(password)) criteria_met++

  return criteria_met >= 3
});
const {getAccessTokenSilently,logout} = useAuth0();
const {profile,idp_partner_users,auth0_users} = storeToRefs(store);

const TempAvatar = (file:any) => {
  if (!file) {
    return;
  }
  const reader = new FileReader();
  reader.readAsDataURL(file);
  reader.onload = () => {
    editable.value.picture = reader.result as string;
  };
  reader.onerror = (error:any) => {
    store.ShowError(error);
  };
};
const UpdateSignature = async()=>{
  const form = new FormData;
  is_loading.value = 'signature';
  const token = await getAccessTokenSilently();
  const endpoint = '/users/update-email-signature';
  form.append('user_id', editable.value.user_id);
  form.append('email_signature', editable.value.email_signature);
  UccServer(token).post(endpoint,form).then(()=>{
    // Refetch and Reset signature model
    store.FetchPartnerUsers(token).then(()=>{
      if (my_partner_user_profile.value) {
        const partner_user_profile = FindPartnerUser(editable.value.user_id);
        editable.value.email_signature = partner_user_profile.email_signature;
      }
    });
    store.ShowSuccess("Email signature updated successfully!");
  }).finally(() => {
    is_loading.value = null;
  });
};
const UpdateProfile = async (loading_target:string) => {
  const form = new FormData;
  const data = {...editable.value};
  const token = await getAccessTokenSilently();
  const title = loading_target.charAt(0).toUpperCase()+loading_target.slice(1);

  // If updating profile, remove password fields.
  if (loading_target=='profile') {
    delete data.new_password;
    delete data.confirm_password;
  }

  // If image didn't change, delete from form
  if (editable.value.picture == profile.value?.picture) {
    delete data.picture;
  }
  else {
    form.append('avatar', file_input.value);
    form.append('default_avatar', profile.value?.picture ?? '');
  }

  is_loading.value = loading_target;
  form.append('profile', JSON.stringify(data));
  UccServer(token).post('/users/update-profile',form).then(async res => {
    console.log(res.data);
    file_input.value = null;
    profile.value = res.data.profile;
    store.FetchUsers(token);
    store.ShowSuccess(`${title} updated successfully!`);
  }).finally(() => {
    is_loading.value = null;
  });
}

onMounted(async () => {
  // Ensure both collections are loaded before reacting
  if (auth0_users.value.length <= 0 || idp_partner_users.value.length <= 0) {
    const token = await getAccessTokenSilently();
    if (auth0_users.value.length <= 0) await store.FetchUsers(token);
    if (idp_partner_users.value.length <= 0) await store.FetchPartnerUsers(token);
  }

  // React when users, partner users, or edit_user changes
  watch([() => auth0_users.value, () => idp_partner_users.value, () => props.edit_user], ([usersVal, partnerUsersVal, editUser]) => {
    if (!usersVal || usersVal.length === 0) return;

    const selectedProfile = editUser ?? usersVal.find((u: any) => u.user_id == my_user_id.value);
    if (!selectedProfile) return;

    // Set base editable fields
    editable.value = {
      user_id:          selectedProfile.user_id,
      firstname:        selectedProfile.user_metadata.first_name,
      lastname:         selectedProfile.user_metadata.last_name,
      email:            selectedProfile.email,
      picture:          selectedProfile.picture,
      new_password:     "",
      confirm_password: "",
      email_signature:  "",
    };

    // Merge email signature when partner_users are available
    if (partnerUsersVal && partnerUsersVal.length > 0) {
      const partner_user_profile = FindPartnerUser(editable.value.user_id);
      if (partner_user_profile) {
        editable.value.email_signature = partner_user_profile.email_signature;
      }
    }
  }, {immediate: true});
});
</script>
