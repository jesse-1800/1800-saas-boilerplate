<template>
</template>

<script lang="ts" setup>
import {useAuth0} from '@auth0/auth0-vue';
const {loginWithRedirect} = useAuth0();
onMounted(() => {
  loginWithRedirect();
});
</script>
