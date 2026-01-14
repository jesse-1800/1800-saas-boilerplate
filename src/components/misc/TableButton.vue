<template>
  <v-btn
    :text="label"
    :style="style"
    :prepend-icon="icon"
    :width="width ?? null"
    :size="size ?? 'small'"
    @click="$emit('onclick')"
    :color="color ?? 'primary'"
    :disabled="disabled ?? false"
    :variant="variant??'elevated'">
  </v-btn>
</template>

<script setup>
defineProps(['style','label','icon','color','width','size','variant','disabled'])
</script>
