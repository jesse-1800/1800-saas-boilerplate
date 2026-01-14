<template>
  <v-text-field
    :type="type"
    hide-details
    elevation="0"
    density="compact"
    variant="outlined"
    :readonly="readonly"
    v-model="internalValue"
    :prepend-inner-icon="icon"
    :class="`table-input ${type}`"
    :min="!is_text ? minimum : undefined"
    :disabled="!is_text ? disabled : undefined">
    <template #prepend-inner>
      <slot name="prepend-inner"/>
    </template>
    <template #append-inner>
      <slot name="append-inner"/>
    </template>
  </v-text-field>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: 0
  },
  type: {
    type: String,
    default: 'text'
  },
  minimum: {
    type: Number,
    default: 0
  },
  readonly: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: null
  }
});
const emit = defineEmits(['update:modelValue']);
const is_text = computed(() => {
  return props.type === 'text'
});
const internalValue = computed({
  get: () => props.modelValue,
  set: (val) => {
    // Only convert to number for non-text inputs
    const value = is_text.value ? val : Number(val)
    emit('update:modelValue', value)
  }
})
</script>

<style lang="scss" scoped>
/* We only want to remove padding if it's text input */
.table-input.text {
  :deep(.v-field) {
    padding-left: 0;
  }
}
</style>
