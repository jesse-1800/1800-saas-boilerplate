<template>
  <v-row>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Transaction ID" :model-value="ucc_filing.ucc_transaction_id" readonly/>
    </v-col>

    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Filing Date" :model-value="ucc_filing.ucc_date" readonly/>
    </v-col>

    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Lease Acq. Date" :model-value="ucc_filing.ucc_lease_acqui_date" readonly/>
    </v-col>

    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Status" :model-value="ucc_filing.ucc_status" readonly/>
    </v-col>

    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Lien" :model-value="ucc_filing.ucc_lien" readonly/>
    </v-col>

    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="Batch" :model-value="ucc_filing.ucc_batch" readonly/>
    </v-col>

    <v-col cols="12">
      <v-textarea :hide-details="condensed" variant="outlined" rows="2" label="Comments" :model-value="ucc_filing.ucc_comments" readonly/>
    </v-col>
  </v-row>
</template>

<script setup>
const {ucc_filing,condensed} = defineProps(['ucc_filing','condensed'])
</script>
