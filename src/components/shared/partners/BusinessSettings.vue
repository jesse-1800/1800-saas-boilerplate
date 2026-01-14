<template>
  <!--Company Settings-->
  <v-expansion-panels model-value="0" class="mt-5">
    <panel class="border" icon="mdi-domain" title="Company Details">
      <v-card-text>
        <v-row>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              required
              class="mt-2"
              variant="outlined"
              label="Company Name"
              v-model="partner_form.name">
            </v-text-field>
            <div class="d-flex align-center mb-10 border pa-5 border-opacity-25 rounded">
              <div class="logo-container border mr-5">
                <v-img
                  width="120"
                  height="120"
                  class="rounded"
                  v-if="partner_form"
                  :src="get_company_logo">
                </v-img>
                <v-icon
                  v-if="partner_form.logo"
                  icon="mdi-close-circle"
                  @click="partner_form.logo=''"
                  class="icon-remove">
                </v-icon>
              </div>
              <div>
                <p>Company Logo</p>
                <p>The proposed size is 512 •512 no bigger than 2MB</p>
                <v-file-input
                  hide-details
                  class="mt-5"
                  density="compact"
                  variant="outlined"
                  label="Click to upload"
                  @update:modelValue="SetLogo">
                </v-file-input>
              </div>
            </div>
          </v-col>

          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              required
              class="mt-2"
              variant="outlined"
              label="Website URL (optional)"
              v-model="partner_form.website">
            </v-text-field>
            <v-row>
              <v-col cols="12" lg="6" md="6" sm="12">
                <v-text-field
                  required
                  class="mt-2"
                  variant="outlined"
                  label="Phone Number (optional)"
                  v-model="partner_form.phone_number">
                </v-text-field>
              </v-col>
              <v-col cols="12" lg="6" md="6" sm="12">
                <v-menu
                  offset-y
                  v-model="color_picker"
                  class="position-relative"
                  :close-on-content-click="false"
                  transition="scale-transition">
                  <template #activator="{props}">
                    <v-text-field
                      v-bind="props"
                      required
                      class="mt-2"
                      variant="outlined"
                      label="Brand Color"
                      v-model="partner_form.brand_color">
                      <template #prepend-inner>
                        <v-card
                          width="30"
                          height="30"
                          :style="{backgroundColor:partner_form.brand_color}">
                        </v-card>
                      </template>
                    </v-text-field>
                  </template>

                  <v-color-picker
                    v-model="partner_form.brand_color"
                    hide-inputs
                    mode="hexa"
                  />
                </v-menu>
              </v-col>
            </v-row>
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
import {GlobalStore} from "@/stores/globals";
import {theme_btn_style} from "@/composables/GlobalComposables";

defineProps(['is_modal']);

const store = GlobalStore();
const color_picker = ref(false);
const get_company_logo = computed(() => {
  const default_logo = 'https://placehold.co/100x100/EEE/31343C?font=oswald&text=logo';
  if (!partner_form.value || partner_form.value.logo == undefined) {
    return default_logo;
  }
  if (!partner_form.value.logo.length) {
    return default_logo;
  }
  return partner_form.value.logo;
});
const SetLogo = (file:any) => {
  if (!file) {
    return;
  }
  const reader = new FileReader();
  reader.readAsDataURL(file);
  reader.onload = () => {
    partner_form.value.logo = reader.result as string;
  };
  reader.onerror = (error) => {
    console.log('Error: ', error);
  };
};
const {partner_form,partner_loading} = storeToRefs(store);
</script>
