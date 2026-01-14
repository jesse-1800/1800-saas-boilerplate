<template>
  <v-btn @click="Login">Login</v-btn>
</template>

<script setup>
import {useAuth0} from '@auth0/auth0-vue';
const {loginWithRedirect} = useAuth0();

const Login = () => {
  loginWithRedirect();
}
</script>
