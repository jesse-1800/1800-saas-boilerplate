<template>
  <AppLayout>
    <template #title>Partners</template>
    <template #content>

      <!--Buttons-->
      <div class="d-flex align-center mb-4">
        <TableSearchBox v-model="search_model"/>
        <v-spacer/>
        <v-btn
          class="mr-2"
          text="Refresh"
          color="primary"
          @click="Refresh"
          :loading="is_loading"
          :style="theme_btn_style"
          prepend-icon="mdi-refresh">
        </v-btn>
        <v-btn
          color="primary"
          text="Add a Partner"
          :style="theme_btn_style"
          prepend-icon="mdi-plus-circle"
          @click="NewPartner">
        </v-btn>
      </div>

      <!--Table-->
      <v-data-table
        class="border"
        :headers="headers"
        :loading="is_loading"
        :style="theme_table_style"
        :items="filtered_partners">
        <template v-slot:item="{item}">
          <tr>
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>
              <v-btn
                text="Edit"
                size="small"
                color="primary"
                variant="outlined"
                @click="Edit(item)"
                prepend-icon="mdi-pencil"
                :style="theme_border_radius">
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <!--Form-->
      <MyModal
        max_width="800"
        color="transparent"
        v-model="partner_modal"
        @close="partner_modal=false"
        :title="`${partner_form.id?my_company_name+' Profile':'Add a Partner'}`">

        <BusinessSettings :is_modal="true"/>
        <MailSettings :is_modal="true"/>
        <BrandSettings :is_modal="true"/>

        <template #footer>
          <v-btn
            width="150"
            type="submit"
            text="Submit"
            color="primary"
            :loading="is_loading"
            class="ma-auto d-block"
            :style="theme_btn_style"
            @click="SubmitForm">
          </v-btn>
        </template>
      </MyModal>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
  import {storeToRefs} from "pinia";
  import {useAuth0} from "@auth0/auth0-vue";
  import {GlobalStore} from "@/stores/globals";
  import type {PartnerType} from "@/types/StoreTypes";
  import {my_company_name, my_user_id, theme_btn_style} from "@/composables/GlobalComposables";
  import {theme_table_style} from "@/composables/GlobalComposables";
  import {theme_border_radius} from "@/composables/GlobalComposables";

  const store = GlobalStore();
  const search_model = ref('');
  const is_loading = ref(false);
  const headers = [
    {title:'ID', value:'id'},
    {title:'Name', value:'name'},
    {title:'Action', value:'action'}
  ];
  const {getAccessTokenSilently} = useAuth0();
  const {partner_form} = storeToRefs(store);
  const {idp_partners} = storeToRefs(store);
  const {partner_modal} = storeToRefs(store);
  const filtered_partners = computed(() => {
    if (!search_model.value) {
      return idp_partners.value;
    }
    const s = search_model.value.toLowerCase();
    return idp_partners.value.filter((item:any) => item.name.toLowerCase().includes(s));
  });

  const Edit = (item:any) => {
    partner_form.value = {...item};
    partner_modal.value = true;
  };
  const Refresh = async () => {
    is_loading.value = true;
    const token = await getAccessTokenSilently();
    store.FetchPartners(token).finally(() => {
      is_loading.value = false;
    });
  }
  const NewPartner = () => {
    partner_modal.value = true;
    partner_form.value = <any>{
      id: null,
      name: '',
      website: '',
      logo: '',
      phone_number: '',
      brand_color: '',
      supported_brands: [],
      is_active: 1,
    };
  };
  const SubmitForm = async () => {
    is_loading.value = true;
    const token = await getAccessTokenSilently();
    store.SubmitPartnerForm(token,my_user_id.value).then(() => {
      partner_modal.value = false;
      Refresh();
    }).finally(() => {
      is_loading.value = false;
    });
  };

  onMounted(async () => {
    Refresh();
  });
</script>
