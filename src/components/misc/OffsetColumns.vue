<template>
  <v-row>
    <v-col cols="12" :lg="columns" :md="columns" :offset-lg="offset" :offset-md="offset" sm="12" offset-sm="0">
      <slot/>
    </v-col>
  </v-row>
</template>

<script>
export default {
    props: ['offset','columns']
}
</script>
