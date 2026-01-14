<template>
  <v-combobox
    hide-details
    elevation="0"
    :items="items"
    density="compact"
    item-title="title"
    :variant="variant"
    item-value="value"
    :return-object="false"
    :classnames="classnames"
    v-model="internalValue">
    <template #prepend-inner>
      <slot name="inner"></slot>
    </template>
  </v-combobox>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  variant: {
    type: String,
    default: 'solo-filled'
  },
  items: {
    type: Array,
    required: true
  },
  classnames: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const internalValue = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val)
})
</script>
