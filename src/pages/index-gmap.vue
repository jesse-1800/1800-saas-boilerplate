<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>

      <GoogleMap
        api-key="AIzaSyBof785oPijCcynKX2ckPT8JKF6LYSKO8g"
        style="width: 100%; height: 500px"
        :center="center"
        :zoom="4"
        @tilesloaded="OnMapLoaded"
      />

    </template>
  </AppLayout>
</template>

<script setup>
import { GoogleMap } from 'vue3-google-map'
import { ref } from 'vue'

const center = ref({ lat: 39.8283, lng: -98.5795 }) // center of USA
let map_instance = null

function OnMapLoaded(event) {
  // Save the map instance
  map_instance = event.map

  map_instance.data.loadGeoJson(
    'https://raw.githubusercontent.com/PublicaMundi/MappingAPI/master/data/geojson/us-states.json'
  )

  // Optional styling
  map_instance.data.setStyle({
    fillColor: '#cde5ff',
    strokeColor: '#003f7f',
    strokeWeight: 1,
    fillOpacity: 0.7
  })
}
</script>
