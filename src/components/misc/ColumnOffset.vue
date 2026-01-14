<template>
  <v-row>
    <v-col cols="12" :lg="columns" :md="columns" sm="12" :offset-lg="offset" :offset-md="offset">
      <slot/>
    </v-col>
  </v-row>
</template>

<script>
export default {
    props: ['offset','columns']
}
</script>
