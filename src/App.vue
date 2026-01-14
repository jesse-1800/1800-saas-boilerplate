<template>
  <v-app>
    <v-main>
      <forbidden v-if="is_access_forbidden"/>
      <router-view v-else/>
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRouter} from "vue-router";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import Forbidden from "@/pages/forbidden.vue";

const store = GlobalStore();
const router = <any>useRouter();
const {isAuthenticated} = useAuth0();
const {getAccessTokenSilently} = useAuth0();
const is_access_forbidden = computed(() => {
  const partner_user = idp_partner_users.value.find(pu => pu.user_id == profile.value?.sub);
  if (partner_user && partner_user.explorer_access == 0) {
    return true; // block access to this domain
  } else {
    return false; // means user can access just fine
  }
});
const {profile,idp_partner_users} = storeToRefs(GlobalStore());

const DetectScreenOnResize = () => {
  store.SetState({is_mobile: window.innerWidth <= 768});
};

onMounted(async() => {
  // Redirect from a dirty Auth0 URL
  const url = new URL(window.location.href);

  // Redirect if Auth0 params in URL
  const hasAuth0Params = url.searchParams.has('code') && url.searchParams.has('state');
  if (hasAuth0Params) {
    return router.push("/dashboard");
  }

  // Set global is_mobile var
  DetectScreenOnResize();
  window.addEventListener('resize', DetectScreenOnResize);
});
onUnmounted(() => {
  window.removeEventListener('resize', DetectScreenOnResize);
});

// Fetches all data when logged in
watch(isAuthenticated, async() => {
  store.global_loading = true;
  const token = await getAccessTokenSilently();
  store.FetchPartnerUsers(token).then(()=> {
    const partner_user = idp_partner_users.value.find((u:any)=>u.user_id==profile.value?.sub);
    if (partner_user) {
      profile.value!.role = partner_user.role ?? "User";
    }
    store.FetchAllData(token).then(() => {
      store.global_loading = false;
    });
  })
});


</script>
