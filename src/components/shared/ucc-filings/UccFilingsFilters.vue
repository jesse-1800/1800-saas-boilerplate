<template>

  <FlexedBetween class="mb-5">
    <h3 class="font-weight-light">
      Filters
    </h3>
    <v-btn
      size="small"
      variant="text"
      class="d-flex"
      @click="ClearFilters"
      text="Clear Filters"
      prepend-icon="mdi-close">
    </v-btn>
  </FlexedBetween>

  <v-text-field
    density="compact"
    variant="outlined"
    placeholder="Search"
    v-model="filters.search"
    prepend-inner-icon="mdi-folder-search"
    @click:append-inner="filters.search=''"
    :append-inner-icon="filters.search? 'mdi-close':''">
  </v-text-field>
  <v-text-field
    :readonly="true"
    density="compact"
    variant="outlined"
    @click="date_modal=true"
    placeholder="Date range..."
    :model-value="date_range_label"
    @click:append-inner="ClearDates"
    prepend-inner-icon="mdi-calendar-month"
    :append-inner-icon="filters.start_date? 'mdi-close':''">
  </v-text-field>
  <v-combobox
    item-value="id"
    density="compact"
    variant="outlined"
    :return-object="false"
    :items="ucc_providers"
    v-model="filters.provider_id"
    item-title="provider_company"
    label="Service Provider"
    placeholder="Service Provider"
    prepend-inner-icon="mdi-briefcase-outline">
  </v-combobox>
  <v-combobox
    item-value="id"
    density="compact"
    variant="outlined"
    :return-object="false"
    :items="ucc_assignees"
    label="Assignee"
    placeholder="Assignee"
    v-model="filters.assignee_id"
    item-title="assignee_company"
    prepend-inner-icon="mdi-bank">
  </v-combobox>
  <v-combobox
    density="compact"
    variant="outlined"
    :items="mapped_statuses"
    v-model="filters.ucc_status"
    label="UCC Status"
    :return-object="false"
    placeholder="UCC Status"
    @click:append-inner="filters.ucc_status=null"
    :append-inner-icon="filters.ucc_status? 'mdi-close':''"
    prepend-inner-icon="mdi-list-status">
  </v-combobox>
  <v-select
    density="compact"
    variant="outlined"
    :items="state_centers"
    v-model="filters.buyer_state"
    label="State"
    item-title="abbrev"
    item-value="abbrev"
    :return-object="false"
    placeholder="State"
    @click:append-inner="filters.buyer_state=null"
    :append-inner-icon="filters.buyer_state?'mdi-close':''"
    prepend-inner-icon="mdi-city">
  </v-select>
  <v-text-field
    :readonly="true"
    density="compact"
    variant="outlined"
    placeholder="Equipment Count..."
    @click="equipment_modal=true"
    :model-value="equipment_label"
    prepend-inner-icon="mdi-printer-outline"
    @click:append-inner="ClearEquipmentCounts"
    :append-inner-icon="equipment_label?'mdi-close':''">
  </v-text-field>

  <MyModal color="transparent" max_width="700" v-model="date_modal" title="Select the date range...">
    <v-row>
      <v-col cols="6">
        <v-date-picker
          hide-weekdays
          title="Start Date"
          v-model="filters.start_date"
          :max="new Date().toISOString()">
        </v-date-picker>
      </v-col>

      <v-col cols="6">
        <v-date-picker
          hide-weekdays
          title="End Date"
          v-model="filters.end_date"
          :min="filters.start_date"
          :max="new Date().toISOString()">
        </v-date-picker>
      </v-col>
    </v-row>

    <template #footer>
      <v-spacer/>
      <v-btn class="mr-1" size="small" color="default" prepend-icon="mdi-close" @click="ClearDates">Clear</v-btn>
      <v-btn class="ml-1" size="small" color="primary" prepend-icon="mdi-check" @click="date_modal=false">Done</v-btn>
      <v-spacer/>
    </template>
  </MyModal>
  <MyModal color="transparent" max_width="500" v-model="equipment_modal" title="Select range Equipment Count...">
    <v-row>
      <v-col cols="6">
        <v-text-field
          variant="outlined"
          type="number"
          :min="1"
          v-model.number="filters.equipment_min"
          label="Equipment Count (min)">
        </v-text-field>
      </v-col>
      <v-col cols="6">
        <v-text-field
          variant="outlined"
          type="number"
          :min="filters.equipment_min"
          v-model.number="filters.equipment_max"
          label="Equipment Count (max)">
        </v-text-field>
      </v-col>
    </v-row>

    <template #footer>
      <v-spacer/>
      <v-btn class="mr-1" size="small" color="default" prepend-icon="mdi-close" @click="ClearEquipmentCounts">Clear</v-btn>
      <v-btn class="ml-1" size="small" color="primary" prepend-icon="mdi-check" @click="equipment_modal=false">Done</v-btn>
      <v-spacer/>
    </template>
  </MyModal>
</template>

<script lang="ts" setup>
import moment from "moment";
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import type {UccFilingFiltersType} from "@/types/StoreTypes";
import {state_centers} from "@/composables/GlobalComposables";

const store = GlobalStore();
const date_modal = ref(false);
const equipment_modal = ref(false);
const mapped_statuses = computed(() => {
  return ucc_statuses.value.map(({ ucc_status }) => ({
    value: ucc_status,
    title: ucc_status,
  }))
});
const equipment_label = computed(() => {
  if (filters.value.equipment_min != null && filters.value.equipment_max != null) {
    return `${filters.value.equipment_min} to ${filters.value.equipment_max} Equipments`
  }
  return null;
});
const date_range_label = computed(() => {
  const { start_date, end_date } = filters.value

  if (!start_date && !end_date) return ''
  if (!start_date) return `Until ${moment(end_date).format('MMM D, YYYY')}`
  if (!end_date) return `From ${moment(start_date).format('MMM D, YYYY')}`

  const start = moment(start_date)
  const end = moment(end_date)

  if (start.isSame(end, 'day')) return start.format('MMM D, YYYY')

  return start.year() === end.year()
    ? `${start.format('MMM D')} - ${end.format('MMM D, YYYY')}`
    : `${start.format('MMM D, YYYY')} - ${end.format('MMM D, YYYY')}`
});
const {ucc_filing_filters:filters} = storeToRefs(store);
const {ucc_providers,ucc_assignees,ucc_statuses} = storeToRefs(store);

const ClearDates = (event:any) => {
  event.stopPropagation()
  filters.value.start_date = "";
  filters.value.end_date = "";
}
const ClearFilters = () => {
  filters.value = <UccFilingFiltersType>{
    search:        null,
    start_date:    "",
    end_date:      "",
    provider_id:   null,
    assignee_id:   null,
    ucc_status:    null,
    buyer_state:   null,
    equipment_min: null,
    equipment_max: null,
  }
}
const ClearEquipmentCounts = (event:any) => {
  event.stopPropagation()
  filters.value.equipment_min = null;
  filters.value.equipment_max = null;
}
</script>
