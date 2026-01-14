<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" md="8" lg="6" class="text-center">
        <v-icon color="primary" size="96">mdi-alert-circle-outline</v-icon>
        <h1 class="text-h3 font-weight-bold mt-4">404 - Page Not Found</h1>
        <p class="text-subtitle-1 mt-2">
          The page you’re looking for doesn’t exist.
        </p>
        <v-btn variant="text" color="primary" class="mt-6" to="/">
          Back to Homepage
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>
