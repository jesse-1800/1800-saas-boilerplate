<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <squeeze class="mt-md-15" columns="8" offset="2">

        <!--Show the Field Mapping Window-->
        <template v-if="has_pending_tasks">
          <v-card-text>
            <h1 class="mb-5">Pending Task: Complete your import</h1>
            <v-row>
              <v-col cols="5">
                <h3 class="font-weight-light mb-5">Database Columns</h3>
                <template v-for="column in ucc_map_columns">
                  <v-text-field
                    :readonly="true"
                    density="compact"
                    variant="outlined"
                    v-if="column.display"
                    :label="column.label"
                    :placeholder="column.description">
                  </v-text-field>
                </template>
              </v-col>
              <v-col cols="2">
                <br><br>
                <template v-for="buyer in ucc_map_columns">
                  <div v-if="buyer.display" class="mt-1 mb-5 d-flex align-center justify-center" style="height:42px">
                    <v-icon>mdi-swap-horizontal</v-icon>
                  </div>
                </template>
              </v-col>
              <v-col cols="5" >
                <h3 class="font-weight-light mb-5">Map to your CSV Data</h3>
                <template v-for="column in ucc_map_columns">
                  <v-combobox
                    density="compact"
                    variant="outlined"
                    v-if="column.display"
                    label="Select a header"
                    :items="column.column=='buyer_id'? modified_headers : target_headers"
                    v-model="column.mapped_to">
                    <template #item="{item}">
                      <v-list-item @click="column.mapped_to = item.value">
                        <v-list-item-title>
                          <span class="mr-2">{{item.title}}</span>
                          <i class="text-grey" v-if="FindSampleValue(item.value)">({{FindSampleValue(item.value)}})</i>
                        </v-list-item-title>
                      </v-list-item>
                    </template>
                  </v-combobox>
                </template>
              </v-col>
            </v-row>
          </v-card-text>

          <div class="text-center mt-5">
            <v-btn
              :loading="is_loading"
              @click="ImportDataToDB"
              text="Ready to Import"
              prepend-icon="mdi-import"
              :style="theme_btn_style">
            </v-btn>
            <div class="mt-3" v-if="is_loading">
              Import in-progress, don't close this tab until completion...
            </div>
          </div>
        </template>

        <!--Show the Uploader Window-->
        <template v-else>
          <h3 class="font-weight-light">Upload your UCC filings in CSV format. After uploading, you will be able to map your CSV columns to the required fields. This helps ensure accurate data import and reduces formatting issues.</h3>
          <v-sheet
            class="d-flex flex-column align-center justify-center pa-8 mt-8"
            elevation="0"
            height="250"
            rounded="lg"
            @dragover.prevent
            @click="TriggerFileInput"
            @drop.prevent="HandleDrop"
            style="background:transparent;border: 2px dashed #9e9e9e; cursor: pointer;">
            <v-progress-circular v-if="is_uploading" :indeterminate="true"/>
            <v-icon v-else size="56" color="grey">mdi-upload</v-icon>
            <div class="text-grey mt-3">
              <span v-if="is_uploading">Uploading. Please wait...</span>
              <span v-else>Drag & drop CSV file here or click to upload</span>
            </div>
            <input
              type="file"
              ref="file_input"
              class="d-none"
              accept=".csv"
              @change="HandleFileSelect"
            />
          </v-sheet>
        </template>

        <!--List all files-->
        <v-data-table class="mt-5" :style="theme_table_style" :items="ucc_files" :headers="headers">
          <template #item="{item}">
            <tr>
              <td>{{item.id}}</td>
              <td>
                <a :href="`files/download/${item.id}`">
                  {{item.name}}
                </a>
              </td>
              <td>
                <span v-if="item.is_imported" class="text-green">
                  <v-icon size="small">mdi-check-circle-outline</v-icon>
                  <span class="ml-2">Completed</span>
                </span>
                <span v-else class="text-orange">
                  <v-icon size="small">mdi-progress-clock</v-icon>
                  <span class="ml-2">In-progress</span>
                </span>
              </td>
            </tr>
          </template>
        </v-data-table>
      </squeeze>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {my_user_id} from "@/composables/GlobalComposables";
import {SluggifyText} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import {my_company_name} from "@/composables/GlobalComposables";
import {theme_btn_style,} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const actual_data = ref([]);
const is_loading = ref(false);
const is_uploading = ref(false);
const target_headers = ref([]);
const headers = [
  {title:"ID",       value: "id",  sortable:true},
  {title:"File name",value: "name",sortable:true},
  {title:"Status",   value: "is_imported", sortable:true},
];
const file_input = ref<any>(null);
const {getAccessTokenSilently} = useAuth0();
const modified_headers = computed(() => {
  return ['[AUTO-GENERATE]', ...target_headers.value];
});
const has_pending_tasks = computed(() => {
  return ucc_files.value.find(f => f.is_imported == 0);
});
const {ucc_files,ucc_map_columns} = storeToRefs(store);

// Grouping data
const ucc_grouped_data = computed(() => {
  const ucc_key = GetBoundColumn('ucc_id');
  const ids_only_group = <any>[];
  const final_list = <any>[];

  actual_data.value.forEach((data:any) => {
    if (!ids_only_group.includes(data[ucc_key])) {
      if (data[ucc_key].length>0) {
        ids_only_group.push(data[ucc_key]);
      }
    }
  });

  ids_only_group.forEach((ucc_id:string,ucc_index:number) => {
    const selected_ucc_rows = actual_data.value.filter(d => d[ucc_key] == ucc_id);
    const first_row = selected_ucc_rows[0];
    const equipments_list = <any>[];
    selected_ucc_rows.forEach(row => {
      equipments_list.push({
        equipment_unit:      row[GetBoundColumn('equipment_unit')],
        equipment_ucc_year:  row[GetBoundColumn('equipment_ucc_year')],
        equipment_number:    row[GetBoundColumn('equipment_number')],
        equipment_brand:     row[GetBoundColumn('equipment_brand')],
        equipment_model:     row[GetBoundColumn('equipment_model')],
        equipment_desc:      row[GetBoundColumn('equipment_desc')],
        equipment_code:      row[GetBoundColumn('equipment_code')],
        equipment_serial_no: row[GetBoundColumn('equipment_serial_no')],
        equipment_size:      row[GetBoundColumn('equipment_size')],
        equipment_end_year:  row[GetBoundColumn('equipment_end_year')],
        equipment_attachment:row[GetBoundColumn('equipment_attachment')],
        equipment_value:     row[GetBoundColumn('equipment_value')],
        equipment_tae:       row[GetBoundColumn('equipment_tae')],
      });
    });

    final_list.push({
      ucc_id: ucc_id,
      ucc_data: {
        ucc_id:              first_row[GetBoundColumn('ucc_id')],
        ucc_transaction_id:  first_row[GetBoundColumn('ucc_transaction_id')],
        ucc_date:            first_row[GetBoundColumn('ucc_date')],
        ucc_lease_acqui_date:first_row[GetBoundColumn('ucc_lease_acqui_date')],
        ucc_status:          first_row[GetBoundColumn('ucc_status')],
        ucc_lien:            first_row[GetBoundColumn('ucc_lien')],
        ucc_comments:        first_row[GetBoundColumn('ucc_comments')],
        ucc_fips2:           first_row[GetBoundColumn('ucc_fips2')],
        ucc_batch:           first_row[GetBoundColumn('ucc_batch')],
      },
      buyer:    {
        buyer_id:       GetOrCreateBuyerID(first_row,GetBoundColumn('buyer_id'),ucc_index),
        buyer_company:  first_row[GetBoundColumn('buyer_company')],
        buyer_adress1:  first_row[GetBoundColumn('buyer_adress1')],
        buyer_adress2:  first_row[GetBoundColumn('buyer_adress2')],
        buyer_city:     first_row[GetBoundColumn('buyer_city')],
        buyer_state:    first_row[GetBoundColumn('buyer_state')],
        buyer_zip:      first_row[GetBoundColumn('buyer_zip')],
        buyer_phone:    first_row[GetBoundColumn('buyer_phone')],
        buyer_website:  first_row[GetBoundColumn('buyer_website')],
        buyer_fax:      first_row[GetBoundColumn('buyer_fax')],
        buyer_fips:     first_row[GetBoundColumn('buyer_fips')],
        buyer_county:   first_row[GetBoundColumn('buyer_county')],
        buyer_sic:      first_row[GetBoundColumn('buyer_sic')],
        buyer_sic_desc: first_row[GetBoundColumn('buyer_sic_desc')],
        buyer_dola:     first_row[GetBoundColumn('buyer_dola')],
        buyer_duns:     first_row[GetBoundColumn('buyer_duns')],
      },
      contacts: {
        buyer_primary_firstname:   first_row[GetBoundColumn('buyer_primary_firstname')],
        buyer_primary_lastname:    first_row[GetBoundColumn('buyer_primary_lastname')],
        buyer_primary_title:       first_row[GetBoundColumn('buyer_primary_title')],
        buyer_primary_email:       first_row[GetBoundColumn('buyer_primary_email')],
        buyer_primary_phone:       first_row[GetBoundColumn('buyer_primary_phone')],
        buyer_primary_address:     first_row[GetBoundColumn('buyer_primary_address')],
        buyer_primary_city:        first_row[GetBoundColumn('buyer_primary_city')],
        buyer_primary_state:       first_row[GetBoundColumn('buyer_primary_state')],
        buyer_primary_zip:         first_row[GetBoundColumn('buyer_primary_zip')],

        buyer_secondary_firstname: first_row[GetBoundColumn('buyer_secondary_firstname')],
        buyer_secondary_lastname:  first_row[GetBoundColumn('buyer_secondary_lastname')],
        buyer_secondary_title:     first_row[GetBoundColumn('buyer_secondary_title')],
        buyer_secondary_email:     first_row[GetBoundColumn('buyer_secondary_email')],
        buyer_secondary_phone:     first_row[GetBoundColumn('buyer_secondary_phone')],
        buyer_secondary_address:   first_row[GetBoundColumn('buyer_secondary_address')],
        buyer_secondary_city:      first_row[GetBoundColumn('buyer_secondary_city')],
        buyer_secondary_state:     first_row[GetBoundColumn('buyer_secondary_state')],
        buyer_secondary_zip:       first_row[GetBoundColumn('buyer_secondary_zip')],
      },
      provider: {
        provider_id:      first_row[GetBoundColumn('provider_id')],
        provider_class:   first_row[GetBoundColumn('provider_class')],
        provider_company: first_row[GetBoundColumn('provider_company')],
        provider_city:    first_row[GetBoundColumn('provider_city')],
        provider_state:   first_row[GetBoundColumn('provider_state')],
      },
      assignee: {
        assignee_id:      first_row[GetBoundColumn('assignee_id')],
        assignee_class:   first_row[GetBoundColumn('assignee_class')],
        assignee_company: first_row[GetBoundColumn('assignee_company')],
        assignee_city:    first_row[GetBoundColumn('assignee_city')],
        assignee_state:   first_row[GetBoundColumn('assignee_state')],
      },
      equipments: equipments_list,
    });
  });

  return final_list;
});

const GetOrCreateBuyerID = (first_row:any,bound_csv_column:string,ucc_index:number) => {
  const unique_buyer_id = `FILL-${Date.now().toString(36).toUpperCase()}-${ucc_index}`;
  if (bound_csv_column == "[AUTO-GENERATE]") {
    return unique_buyer_id;
  } else {
    return first_row[bound_csv_column];
  }
}
const HandleDrop = (event:any) => {
  if (is_uploading.value) {
    return store.ShowError("An upload is still in progress...")
  }
  const dropped_file = event.dataTransfer.files[0]
  if (dropped_file) {
    if (dropped_file.name.endsWith('.xlsx')) {
      event.target.value = ''
      return store.ShowError('Only CSV files are allowed.')
    }
    UploadToGCS(dropped_file)
  }
}
const UploadToGCS = async(file_obj:any) => {
  const form = new FormData;
  const token = await getAccessTokenSilently();

  is_uploading.value = true;
  form.append('file', file_obj);
  form.append('user_id', my_user_id.value);
  form.append('partner_id', my_partner_id.value);
  form.append('folder_name', SluggifyText(<any>my_company_name.value));

  UccServer(token).post("/import/upload-to-gcs",form).then(()=>{
    store.ShowSuccess("File has been uploaded!")
    store.FetchFiles(token);
  }).finally(() => {
    is_uploading.value = false;
  });
}
const ParseContents = async() => {
  const file_id = has_pending_tasks.value.id;
  const token = await getAccessTokenSilently();
  UccServer(token).post(`/files/parse-file/${file_id}`).then(res => {
    console.log(res.data);
    actual_data.value = res.data.data;
    target_headers.value = res.data.headers;
    ucc_map_columns.value.forEach((column: any) => {
      const preselect = res.data.headers.find((h:string) => {
        const clean_header = h.replace(/^\uFEFF/,'').trim();
        return column.preselect.includes(clean_header);
      });
      if (preselect) {
        column.mapped_to = preselect;
      }
    });
  });
}
const ImportDataToDB = async() => {
  const form = new FormData;
  const file_id = has_pending_tasks.value.id;
  const token = await getAccessTokenSilently();
  const required_map = ['buyer_id','ucc_id'];
  const has_errors = <any>[];

  ucc_map_columns.value.forEach((column: any) => {
    if (required_map.includes(column.column) && !column.mapped_to) {
      has_errors.push(`'${column.label}' is required.`);
    }
  });
  if (has_errors.length > 0) {
    return store.ShowError(has_errors.join('<br>'));
  }
  is_loading.value = true;

  // We're delaying this so browser would
  // show the progress already instead of hang.
  setTimeout(() => {
    form.append('partner_id', my_partner_id.value);
    form.append('file_id', file_id);
    form.append('data', JSON.stringify(ucc_grouped_data.value));
    UccServer(token).post(`/import/import-data`,form).then(res => {
      console.log(res.data);
      store.ShowSuccess(res.data.message);
      store.FetchAllData(token);
    }).finally(() => {
      is_loading.value = false;
    });
  },300);
}
const FindSampleValue = (csv_header:string) => {
  const single_row = actual_data.value[0];
  if (single_row[csv_header]) {
    return single_row[csv_header];
  }
  return null;
}
const GetBoundColumn = (db_column:string) => {
  // this returns the csv column bound to the table column. e.g. ucc_id => UCCID
  const finder = ucc_map_columns.value.find((c:any) => c.column === db_column);
  if (finder) {
    return finder.mapped_to;
  }
  return null;
}
const TriggerFileInput = () => {
  if (is_uploading.value) {
    return store.ShowError("An upload is still in progress...")
  }
  file_input.value?.click()
}
const HandleFileSelect = (event:any) => {
  const selected_file = event.target.files[0]
  if (selected_file) {
    if (selected_file.name.endsWith('.xlsx')) {
      event.target.value = ''
      return store.ShowError('Only CSV files are allowed.')
    }
    UploadToGCS(selected_file)
  }
}

// Watcher to check if there's pending task.
watch(()=>has_pending_tasks.value,(new_val:boolean) => {
  if (new_val) {
    console.log("Fetch ran..")
    ParseContents();
  }
},{immediate:true});
</script>
