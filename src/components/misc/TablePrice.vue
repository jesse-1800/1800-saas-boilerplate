<template>
  <p style="white-space: nowrap;font-size:16px;font-weight:500">
    <v-icon class="mr-3" size="small" v-if="icon">{{icon}}</v-icon>
    $<slot></slot>
  </p>
</template>
<script setup>

defineProps(['icon'])
</script>
