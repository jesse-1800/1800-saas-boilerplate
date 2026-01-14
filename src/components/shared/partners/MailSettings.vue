<template>
  <!--SMTP Settings-->
  <v-expansion-panels model-value="0" class="mt-5">
    <panel class="border" icon="mdi-email-multiple-outline" title="SMTP Settings">
      <v-card-text>
        <v-row>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              hide-details
              variant="outlined"
              v-model="partner_form.smtp_host"
              label="SMTP Host"
              required>
            </v-text-field>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              hide-details
              variant="outlined"
              v-model="partner_form.smtp_port"
              label="SMTP Port"
              required>
            </v-text-field>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              hide-details
              variant="outlined"
              v-model="partner_form.smtp_username"
              label="SMTP Username"
              required>
            </v-text-field>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              hide-details
              variant="outlined"
              v-model="partner_form.smtp_password"
              label="SMTP Password"
              :type="show_password?'text':'password'"
              required>
              <template #append-inner>
                <v-icon @click="show_password=!show_password" v-if="show_password" icon="mdi-eye-circle-outline"/>
                <v-icon @click="show_password=!show_password" v-else icon="mdi-eye-closed"/>
              </template>
            </v-text-field>
          </v-col>
        </v-row>
      </v-card-text>
      <template v-if="!is_modal" #footer>
        <v-btn
          width="150"
          type="submit"
          text="Save"
          color="primary"
          class="ma-auto d-block"
          @click="$emit('submit')"
          :style="theme_btn_style"
          :loading="partner_loading">
        </v-btn>
      </template>
    </panel>
  </v-expansion-panels>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.ts";
import {theme_btn_style} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const is_loading = ref(false);
const show_password = ref(false);
const {partner_form,partner_loading} = storeToRefs(store);

defineProps(['is_modal','is_loading']);
</script>
