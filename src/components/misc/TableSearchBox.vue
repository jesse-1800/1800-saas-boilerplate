<template>
  <v-text-field
    hide-details
    variant="outlined"
    max-width="250"
    density="compact"
    label="Search..."
    rounded="lg"
    prepend-inner-icon="mdi-magnify"
    :model-value="modelValue"
    @update:modelValue="OnInput"
  />
</template>

<script lang="ts" setup>
import { defineProps, defineEmits } from 'vue';
const props = defineProps<{modelValue: string}>();
const emit = defineEmits<{(e:'update:modelValue',value:string):void}>();
const OnInput = (value: string) => {
  emit('update:modelValue', value);
}
</script>
