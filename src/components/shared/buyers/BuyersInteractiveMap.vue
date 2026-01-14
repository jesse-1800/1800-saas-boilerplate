<template>
  <InnerLayout :sidebar="sidebar">
    <template #sidebar>
      <v-card-text>
        <flexed-between class="mb-5">
          <h3 class="font-weight-light">
            ({{selected_state?.count}}) Customers in {{selected_state?.state}}
          </h3>
          <v-btn
            size="x-small"
            elevation="0"
            variant="tonal"
            icon="mdi-close"
            @click="CloseSidebar">
          </v-btn>
        </flexed-between>
        <v-expansion-panels variant="accordion" v-model="selected_city" elevation="0">
          <panel v-for="city in selected_state?.cities" :value="city">
            <template #title>
              <span>{{city.city}}, {{selected_state.state}}</span>
              <v-chip class="position-absolute" style="right:50px" size="x-small"> {{city.count}}</v-chip>
            </template>
            <v-card elevation="0" class="pa-1">
              <div class="text-center" v-if="buyers_loading">
                <v-progress-circular class="mr-2" size="20" indeterminate/>
                <span style="position:relative;top:2px">Loading customers...</span>
              </div>

              <v-list class="pa-1" density="compact">
                <v-list-item class="border-b" density="compact" @click="ViewBuyer(buyer.id)" v-for="buyer in buyers_data">
                  <flexed-between>
                    <div>{{buyer.buyer_company.length? buyer.buyer_company : '(Unknown)'}}</div>
                    <v-icon size="x-small">mdi-open-in-new</v-icon>
                  </flexed-between>
                </v-list-item>
              </v-list>
            </v-card>
          </panel>
        </v-expansion-panels>
      </v-card-text>
    </template>
    <template #content>
      <div v-if="is_loading" class="text-center ma-auto my-16" style="margin-top: 200px;max-width:300px">
        <v-progress-linear indeterminate color="primary" size="150"/>
        <h3 class="font-weight-light">Loading map data...</h3>
      </div>
      <div ref="map_container" style="width: 100%; height: 800px"></div>
    </template>
  </InnerLayout>
  <UccBuyerViewer :buyer_id="view_buyer_id"/>
</template>

<script lang="ts" setup>
declare const google: any;
interface StateData {
  state: string;
  count: number;
  cities: any[];
}

import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {state_centers} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";

const map = ref<any>(null);
const sidebar = ref(false);
const store = GlobalStore();
const is_loading = ref(false);
const map_container = ref(null);
const buyers_loading = ref(false);
const city_markers = ref<any>([]);
const state_markers = ref<any>([]);
const buyers_data = ref<any[]>([]);
const selected_city = ref<any>(null);
const view_buyer_id = ref<any>(null);
const selected_state = ref<any>(null);
const state_data = ref<StateData[]>([]);
const {getAccessTokenSilently} = useAuth0();
const {modals,global_loading} = storeToRefs(store);

const ViewBuyer = (buyer_id:string) => {
  modals.value.customer_profile = true;
  view_buyer_id.value = buyer_id;
}
const CloseSidebar = () => {
  sidebar.value = false;
  map.value.setCenter({ lat: 39.8283, lng: -98.5795 });
  map.value.setZoom(4.4);
  ShowStateMarkers();
  HideCityMarkers();
}
const InitializeMap = () => {
  // Initialize the map with transparent background
  map.value = new google.maps.Map(map_container.value, {
    center: { lat: 39.8283, lng: -98.5795 },
    zoom:    4.4,
    minZoom: 4.4,
    maxZoom: 10,
    disableDefaultUI: true,
    mapTypeControl: false,
    backgroundColor: 'transparent',
    styles: [
      {
        featureType: "all",
        elementType: "all",
        stylers: [{ visibility: "off" }]
      }
    ]
  })

  // Restrict map bounds to US only
  const usBounds = new google.maps.LatLngBounds(
    new google.maps.LatLng(24.396308, -125.0), // Southwest
    new google.maps.LatLng(49.384358, -66.93457) // Northeast
  )
  map.value.setOptions({
    restriction: {
      latLngBounds: usBounds,
      strictBounds: false
    }
  })

  // Load GeoJSON
  map.value.data.loadGeoJson(
    'https://raw.githubusercontent.com/PublicaMundi/MappingAPI/master/data/geojson/us-states.json'
  )

  // Style the states
  map.value.data.setStyle({
    fillColor: '#1c54a8',
    strokeColor: '#a9d0fd',
    strokeWeight: 1,
    fillOpacity: 0.7
  })

  // Add custom state labels for states (just labels)
  state_centers.forEach(state_center => {
    // We will not display this label if state data has value
    // because the state data creates a new label for itself.
    if (state_data.value.filter(s=>s.state===state_center.abbrev).length>0) return;
    new google.maps.Marker({
      position: { lat: state_center.lat, lng: state_center.lng },
      map: map.value,
      label: {
        text: state_center.abbrev,
        color: 'white',
        fontSize: '11px',
        fontWeight: '600'
      },
      icon: {
        path: 'M 0,0',
        strokeOpacity: 0,
        scale: 1
      }
    })
  });
}
const FetchBuyersData = async () => {
  is_loading.value = true;
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/data/map-data/${my_partner_id.value}`).then(res=>{
    state_data.value = res.data;
    InitializeMap();
    AddStateMarkers();
  }).finally(()=> {
    is_loading.value = false;
  });
}
const HideStateMarkers = () => {
  state_markers.value.forEach((marker: any) => {
    if (marker && marker.setVisible) {
      marker.setVisible(false);
    }
  });
}
const ShowStateMarkers = () => {
  state_markers.value.forEach((marker: any) => {
    if (marker && marker.setVisible) {
      marker.setVisible(true);
    }
  });
}
const AddStateMarkers = () => {
  // Add markers with red circles ONLY for states
  // This will put a new label on top of the original one.
  state_data.value.forEach(state_item => {
    const state_center = state_centers.find(s => s.abbrev === state_item.state)
    if (state_center) {

      // Outer ripple layer (largest, most transparent)
      const outer:any = new google.maps.Marker({
        position: { lat: state_center.lat, lng: state_center.lng },
        map: map.value,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 0.2,
          strokeWeight: 0,
          scale: 45
        },
        clickable: false
      })

      // Middle ripple layer
      const middle:any = new google.maps.Marker({
        position: { lat: state_center.lat, lng: state_center.lng },
        map: map.value,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 0.4,
          strokeWeight: 0,
          scale: 37
        },
        clickable: false
      })

      // Inner circle with number (Contains the value itself)
      const inner:any = new google.maps.Marker({
        position: { lat: state_center.lat, lng: state_center.lng },
        map: map.value,
        label: {
          text: `${state_item.state} (${state_item.count.toString()})`,
          color: 'white',
          fontSize: '12px',
          fontWeight: 'bold'
        },
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 1,
          strokeWeight: 0,
          scale: 30
        }
      });
      inner.addListener('click', () => {
        global_loading.value = true;
        selected_state.value = state_item;
        sidebar.value = true;
        map.value.setZoom(7);
        requestAnimationFrame(() => {
          map.value.setCenter({ lat: state_center.lat, lng: state_center.lng });
          map.value.setZoom(7);
          setTimeout(() => HideStateMarkers(),100);
          AddCityMarkers(state_item);
        });
      });

      // Store all three markers so we can remove them easily
      state_markers.value.push(outer);
      state_markers.value.push(middle);
      state_markers.value.push(inner);
    }
  })
}
const HideCityMarkers = () => {
  city_markers.value.forEach((marker: any) => {
    if (marker && marker.setVisible) {
      marker.setVisible(false);
    }
  });
}
const AddCityMarkers = async (state_item:StateData) => {
  const geocoder = new google.maps.Geocoder();
  const results = [];

  for (const city of state_item.cities) {
    try {
      const result = await geocoder.geocode({
        address: `${city.city}, ${state_item.state}, USA`
      });

      if (result.results[0]) {
        const location = result.results[0].geometry.location;
        results.push({
          city: city.city,
          lat: location.lat(),
          lng: location.lng(),
          count: city.count,
        });
      }

      // Add delay to avoid rate limiting (50 requests/sec limit)
      await new Promise(resolve => setTimeout(resolve, 100));

    } catch (error) {
      console.error(`Error geocoding ${city}:`, error);
    }
  }
  results.forEach(city => {
    // Outer ripple layer (largest, most transparent)
    const outer:any = new google.maps.Marker({
      position: { lat: city.lat, lng: city.lng },
      map: map.value,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: 'rgb(255,60,24)',
        fillOpacity: 0.2,
        strokeWeight: 0,
        scale: 45
      },
      clickable: false
    })

    // Middle ripple layer
    const middle:any = new google.maps.Marker({
      position: { lat: city.lat, lng: city.lng },
      map: map.value,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: 'rgb(255,60,24)',
        fillOpacity: 0.4,
        strokeWeight: 0,
        scale: 37
      },
      clickable: false
    })

    // Inner circle with number (Contains the value itself)
    const inner:any = new google.maps.Marker({
      position: { lat: city.lat, lng: city.lng },
      map: map.value,
      label: {
        text: `${city.city} (${city.count.toString()})`,
        color: 'white',
        fontSize: '11px',
        fontWeight: 'bold'
      },
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: 'rgb(255,60,24)',
        fillOpacity: 1,
        strokeWeight: 0,
        scale: 30
      }
    });
    inner.addListener('click', () => {
      console.log("city: ", city)
      selected_city.value = {
        city: city.city,
        count: city.count
      };
    });

    // Store all three markers so we can remove them easily
    city_markers.value.push(outer);
    city_markers.value.push(middle);
    city_markers.value.push(inner);
  });

  global_loading.value = false;
}


// Fetch customers by state->city
watch(selected_city, async (new_city)=>{
  if (new_city) {
    const form = new FormData;
    const city = selected_city.value.city;
    const state = selected_state.value.state;
    const token = await getAccessTokenSilently();
    form.append('city',city);
    form.append('state',state);
    buyers_loading.value = true;
    UccServer(token).post(`/buyers/find-by-city/${my_partner_id.value}`,form).then(res=>{
      console.log(res.data);
      buyers_data.value = res.data;
    }).finally(() => {
      buyers_loading.value = false;
    });
  }
});

// Wait for partner users to be available
watch(my_partner_id,(new_val) => {
  if (new_val) FetchBuyersData();
},{immediate:true});
</script>
