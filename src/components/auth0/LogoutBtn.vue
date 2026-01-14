<template>
  <v-btn prepend-icon="mdi-logout" @click="Logout">Logout</v-btn>
</template>

<script setup>
import {useAuth0} from '@auth0/auth0-vue';
import {GlobalStore} from "@/stores/globals";
const {logout} = useAuth0();
const store = GlobalStore();
const {SetProfile} = store;

const Logout = () => {
  SetProfile(null);
  logout({
    logoutParams: {
      returnTo: import.meta.env.VITE_AUTH0_REDIRECT
    }
  })
}
</script>
