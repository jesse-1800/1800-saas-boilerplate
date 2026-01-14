<template>
  <template v-if="tooltip">
    <v-btn
      icon="icon"
      size="x-small"
      color="primary"
      v-tooltip="tooltip"
      @click="$emit('onclick')">
    </v-btn>
  </template>
  <template v-else>
    <v-btn
      icon="icon"
      size="x-small"
      color="primary"
      @click="$emit('onclick')">
    </v-btn>
  </template>
</template>

<script setup>
defineProps(['label','tooltip'])
</script>
