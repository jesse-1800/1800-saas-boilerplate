<template>
  <!--Brand Settings-->
  <v-expansion-panels model-value="0" class="mt-5">
    <panel class="border" icon="mdi-printer-outline" title="Supported Brands">
      <v-card-text>
        <v-row>
          <v-col cols="6">
            <v-checkbox-btn
              v-for="item in cat_manufacturers.slice(0,Math.ceil(cat_manufacturers.length/2))"
              :key="item.id as any"
              :model-value="IsBrandAdded(item)"
              @update:model-value="ToggleBrand($event, item)"
              :label="item.name"
            />
          </v-col>
          <v-col cols="6">
            <v-checkbox-btn
              v-for="item in cat_manufacturers.slice(Math.ceil(cat_manufacturers.length/2))"
              :key="item.id as any"
              :model-value="IsBrandAdded(item)"
              @update:model-value="ToggleBrand($event, item)"
              :label="item.name"
            />
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
import {theme_btn_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {partner_form,cat_manufacturers,partner_loading} = storeToRefs(store);

const IsBrandAdded = computed(() => (brand: any) =>
  partner_form.value?.supported_brands?.includes(brand.id) ?? false
)
const ToggleBrand = async ($event: any, brand: any) => {
  if ($event && !IsBrandAdded.value(brand)) {
    partner_form.value.supported_brands.push(brand.id);
  } else {
    if (IsBrandAdded.value(brand)) {
      const index = partner_form.value.supported_brands.findIndex((item: any) => item === brand.id);
      partner_form.value.supported_brands.splice(index, 1);
    }
  }
}

defineProps(['is_modal']);
</script>
