<template>
  <v-app :style="theme_main_background" id="backend-app" :theme="backend_theme" class="outfit-font">
    <v-layout class="d-flex flex-column fill-height">
      <v-app-bar class="layout-appbar elevation-0 border-b" prominent :style="theme_navbar_style">
        <v-app-bar-nav-icon variant="text" @click="ToggleSidebar"/>
        <AuthorLogoBackend/>
        <v-spacer/>

        <div class="d-none d-md-block">
          <div class="d-flex align-center" v-if="global_loading">
            <div>Fetching data...</div>
          </div>
          <div v-else>
            <span>{{my_company_name ?? 'Loading'}} &mdash; </span>
            <slot name="title"/>
          </div>
        </div>

        <v-spacer/>
        <ThemeSwitcher/>
        <LoginBtn v-if="!profile"/>
        <LogoutBtn v-if="profile"/>
      </v-app-bar>

      <v-navigation-drawer :style="theme_drawer_style" class="layout-sidebar" v-model="sidebar" location="left" prominent>
        <v-list v-if="profile">
          <v-list-item
            :title="profile.name"
            :subtitle="profile.email"
            :prepend-avatar="profile.picture">
            <small class="text-grey-lighten-1"><i>{{profile.role}}</i></small>
          </v-list-item>
        </v-list>

        <v-list>
          <v-list-item to="/"            prepend-icon="mdi-view-dashboard" title="Dashboard"/>
          <v-list-item to="/ucc-filings" prepend-icon="mdi-format-list-text" title="UCC Filings"/>
          <v-list-item to="/customers"   prepend-icon="mdi-account-supervisor-circle" title="Customers"/>
          <v-list-item to="/contacts"    prepend-icon="mdi-account-supervisor" title="Contacts"/>
          <v-list-item to="/import"      prepend-icon="mdi-import" title="Import Data..."/>

          <template v-if="is_admin">
            <v-divider/>
            <v-list-item to="/partners" prepend-icon="mdi-domain" title="Partners"/>
            <v-list-item to="/users" prepend-icon="mdi-account-group-outline" title="Users"/>
          </template>
        </v-list>

        <slot name="sidebar"/>
      </v-navigation-drawer>

      <v-main style="background:none">
        <v-card-text style="background:none">
          <slot name="content"/>
        </v-card-text>
      </v-main>

      <v-footer app :class="`layout-footer border-t`" v-if="$slots.footer" :style="theme_table_style">
        <slot name="footer"/>
      </v-footer>
    </v-layout>

    <ConfirmDialog/>
    <AdminOverlay/>
    <Snackbar/>
  </v-app>
</template>

<script lang="ts" setup>
import {storeToRefs} from 'pinia';
import {GlobalStore} from "@/stores/globals";
import {is_admin, my_partner_id} from "@/composables/GlobalComposables";
import {
  my_company_name,
  theme_table_style,
  theme_drawer_style,
  theme_navbar_style,
  theme_main_background
} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {SetState} = store;
const {sidebar,profile,backend_theme} = storeToRefs(store);
const {global_loading} = storeToRefs(store);

const ToggleSidebar = () => {
  SetState({sidebar:!sidebar.value});
}
</script>
<style lang="scss">

</style>
