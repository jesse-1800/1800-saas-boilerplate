<template>
  <v-app>
    <v-app-bar>
      <v-app-bar-title>
        <slot name="title"></slot>
      </v-app-bar-title>
    </v-app-bar>

    <v-navigation-drawer>
      <v-list>
        <v-list-item prepend-icon="mdi-home" title="Homepage" href="/"></v-list-item>
        <v-list-item prepend-icon="mdi-account" title="Profile" href="/profile"></v-list-item>
        <v-list-item prepend-icon="mdi-cog" title="Settings" href="/settings"></v-list-item>
        <v-list-item prepend-icon="mdi-account-wrench-outline" title="Services" href="/services"></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container>
        <slot name="content"></slot>
      </v-container>
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>

</script>
