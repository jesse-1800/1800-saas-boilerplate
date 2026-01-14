<template>
  <div class="theme-icon">
    <v-icon>{{icon}}</v-icon>
    <span class="menu-name">
      <slot></slot>
    </span>
    <v-chip size="small" class="the-chip" v-if="chip !== undefined">
      {{chip}}
    </v-chip>
  </div>
</template>

<script setup>
const props = defineProps(['icon','chip']);
</script>
<style lang="scss" scoped>
.theme-icon {
  .menu-name {
    padding-left: 15px;
    position: relative;
    top: -6px;
  }
  .the-chip {
    position: absolute;
    right: 20px;
    top: 11px;
  }
}
</style>
