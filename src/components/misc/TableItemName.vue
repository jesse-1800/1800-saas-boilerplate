<template>
  <div class="d-flex align-center">
    <v-btn
      v-if="icon"
      size="small"
      :icon="icon"
      elevation="0"
      variant="text"
      density="comfortable"
      @click="$emit('onclick')">
    </v-btn>
    <div class="ml-1">
      <slot/>
    </div>
  </div>
</template>

<script setup>
const props = defineProps(['icon'])
</script>
