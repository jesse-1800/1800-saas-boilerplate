import {storeToRefs} from "pinia";
import moment from "moment/moment";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {
  modals,
  profile,
  auth0_users,
  backend_theme,
  idp_partners,
  idp_partner_users,

  ucc_files,
  ucc_buyers,
  ucc_filings,
  ucc_equipments,
} = storeToRefs(store);

// Computed properties
export const my_profile = computed(() => {
  return profile.value;
});
export const my_user_id = computed(() => {
  if (!profile.value) return ("");
  return profile.value?.sub
});
export const my_partner_id = computed(() => {
  if (!profile.value) return null;
  const partner = idp_partner_users.value?.find((pu:any) => {
    return pu.user_id == profile.value?.sub;
  });
  return partner ? partner.partner_id : null;
});
export const my_partner_object = computed(() => {
  if (!profile.value) return null;
  const partner = idp_partner_users.value.find((pu:any) => {
    return pu.user_id == profile.value?.sub;
  });
  return idp_partners.value.find((p:any) => p.id == partner?.partner_id);
});
export const my_company_name = computed(() => {
  if (!idp_partners.value) return null;
  if (!my_partner_id.value) return null;
  const partner = idp_partners.value?.find((p:any) => {
    return p.id == my_partner_id.value
  });
  return partner? partner.name : null;
});
export const my_files = computed(() => {
  return ucc_files.value.filter(f => f.partner_id == my_partner_id.value);
});
export const my_users = computed(() => {
  return auth0_users.value.filter((u:any) => {
    return IsUserInMyTeam(u.user_id) && u.role != "Admin";
  });
});
export const my_partner_user_profile = computed(() => {
  return idp_partner_users.value.find((pu:any) => {
    return pu.user_id == my_user_id.value;
  });
});

// Methods
export const ToggleModal = (name: string,value:boolean) => {
  modals.value[name] = value;
};
export const IsUserInMyTeam = (user_id:string) => {
  const matches = idp_partner_users.value.filter((pu:any) => {
    return pu.user_id == user_id && pu.partner_id == my_partner_id.value;
  });
  return matches.length > 0;
}
export const ParseValidName = (name:string) => {
  const parts = name.trim().split(/\s+/);
  if (!parts?.length) {
    return { firstname: '', lastname: '' }
  }
  const lastname = parts.pop()!;
  const firstname = parts.join(' ');
  return { firstname, lastname };
}
export const FindPartnerUser = (user_id:string) => {
  return idp_partner_users.value.find((pu:any) => pu.user_id == user_id);
}
export const IsMyself = (user_id:string) => {
  return user_id == my_user_id.value;
}
export const SluggifyText = (input_text:string) => {
  return input_text
  .toString()
  .trim()
  .toLowerCase()
  .replace(/[\s\_]+/g, "-")        // replace spaces/underscores with hyphens
  .replace(/[^a-z0-9\-]/g, "")     // remove non-alphanumeric chars except hyphens
  .replace(/\-+/g, "-")            // collapse multiple hyphens
  .replace(/^\-+|\-+$/g, "");      // trim leading/trailing hyphens
}
export const FindUccEquipments = (ucc_filing_id:string) => {
  return ucc_equipments.value.filter(equipment => {
    return equipment.ucc_filing_id === ucc_filing_id
  });
}
export const FindUccBuyer = (buyer_id:string) => {
  return ucc_buyers.value.find(buyer => buyer.id === buyer_id);
}

// Access Levels
export const is_admin = computed(() => {
  return profile.value?.role?.toLowerCase().includes('admin');
});
export const is_manager = computed(() => {
  return profile.value?.role.toLowerCase().includes('manager');
});

// Theming

export const cm8_gradient = ref('linear-gradient(44deg,rgba(4,0,110,1) 0%,rgba(0,0,171,1) 51%,rgba(0,100,200,1) 100%)');
export const style_defaults = ref({
  border_radius: '5px',

  // For buttons
  elems_light_theme_gradient: 'linear-gradient(45deg,#5198D8,#7C95ED,#BBA9FC)',
  elems_dark_theme_gradient: 'linear-gradient(44deg,rgba(4, 0, 110, 1) 0%, rgba(0, 0, 171, 1) 51%, rgba(0, 100, 200, 1) 100%)',

  // For body background
  body_light_theme_gradient: '#f1f1f1',
  body_dark_theme_gradient: 'radial-gradient(circle,rgba(13, 11, 79, 1) 0%, rgba(11, 11, 54, 1) 48%, rgba(0, 7, 23, 1) 100%)',
})
export const theme_main_background = computed(() => {
  const gradients = <any>{
    light: style_defaults.value.body_light_theme_gradient,
    dark: style_defaults.value.body_dark_theme_gradient,
  }
  return `background: ${gradients[backend_theme.value]}`;
});
export const theme_border_radius = ref(`border-radius:${style_defaults.value.border_radius}`);
export const theme_switch_background = computed(() => {
  const gradients = <any>{
    light: cm8_gradient.value,
    dark: cm8_gradient.value,
  }
  return `background: ${gradients[backend_theme.value]}`;
});
export const theme_btn_style = computed(() => {
  const gradients = <any>{
    light: {
      background: cm8_gradient.value,
      color: 'white !important',
      border: 'none',
      borderRadius: style_defaults.value.border_radius,
      boxShadow: 'none',
    },
    dark: {
      background: cm8_gradient.value,
      color: 'white !important',
      border: 'none',
      borderRadius: style_defaults.value.border_radius,
      boxShadow: 'none',
    }
  }

  return gradients[backend_theme.value];
});
export const theme_table_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      ${theme_border_radius.value}
    `)
  } else {
    return (`
      background:linear-gradient(57deg,rgba(5, 4, 36, 1) 0%, rgba(6, 6, 59, 1) 100%);
      ${theme_border_radius.value}
    `)
  }
});
export const theme_card_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background: white;
      ${theme_border_radius.value}
    `)
  } else {
    return (`
      background:linear-gradient(57deg,rgba(5, 4, 36, 1) 0%, rgba(6, 6, 59, 1) 100%);
      ${theme_border_radius.value}
    `)
  }
});
export const theme_card_title_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background:${cm8_gradient.value};
      color: #ffffff;
    `)
  } else {
    return `background:rgba(28, 33, 65, 0.8)`
  }
});
export const theme_drawer_style = computed(() => {
  if (backend_theme.value == 'light') {
    return ``
  } else {
    return 'background:#060927'
  }
});
export const theme_navbar_style = computed(() => {
  if (backend_theme.value == 'light') {
    return ``
  } else {
    return `background:rgba(7,10,42,0.8)`
  }
});
export const theme_modal_style = computed(() => {
  if (backend_theme.value == 'light') {
    return 'background: #ffffff'
  } else {
    return 'background: linear-gradient(57deg,rgba(5,4,36,1) 0%, rgba(6,6,59,1) 100%)'
  }
});
export const theme_panel_color = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background: #f1f1f1;
    `)
  } else {
    return `background:rgba(28, 33, 65, 0.8)`
  }
});

// Timestamps
export const timestamp_now = ref(moment().format("YYYY-MM-DD hh:mm A"));

// Google Maps Specifics
interface StateCenter {
  name: string;
  lat: number;
  lng: number;
  abbrev: string;
}
export const state_centers:StateCenter[] = [
  { name: "Alabama", lat: 32.806671, lng: -86.791130, abbrev: "AL" },
  { name: "Alaska", lat: 61.370716, lng: -152.404419, abbrev: "AK" },
  { name: "Arizona", lat: 33.729759, lng: -111.431221, abbrev: "AZ" },
  { name: "Arkansas", lat: 34.969704, lng: -92.373123, abbrev: "AR" },
  { name: "California", lat: 36.116203, lng: -119.681564, abbrev: "CA" },
  { name: "Colorado", lat: 39.059811, lng: -105.311104, abbrev: "CO" },
  { name: "Connecticut", lat: 41.597782, lng: -72.755371, abbrev: "CT" },
  { name: "Delaware", lat: 39.318523, lng: -75.507141, abbrev: "DE" },
  { name: "Florida", lat: 27.766279, lng: -81.686783, abbrev: "FL" },
  { name: "Georgia", lat: 33.040619, lng: -83.643074, abbrev: "GA" },
  { name: "Hawaii", lat: 21.094318, lng: -157.498337, abbrev: "HI" },
  { name: "Idaho", lat: 44.240459, lng: -114.478828, abbrev: "ID" },
  { name: "Illinois", lat: 40.349457, lng: -88.986137, abbrev: "IL" },
  { name: "Indiana", lat: 39.849426, lng: -86.258278, abbrev: "IN" },
  { name: "Iowa", lat: 42.011539, lng: -93.210526, abbrev: "IA" },
  { name: "Kansas", lat: 38.526600, lng: -96.726486, abbrev: "KS" },
  { name: "Kentucky", lat: 37.668140, lng: -84.670067, abbrev: "KY" },
  { name: "Louisiana", lat: 31.169546, lng: -91.867805, abbrev: "LA" },
  { name: "Maine", lat: 44.693947, lng: -69.381927, abbrev: "ME" },
  { name: "Maryland", lat: 39.063946, lng: -76.802101, abbrev: "MD" },
  { name: "Massachusetts", lat: 42.230171, lng: -71.530106, abbrev: "MA" },
  { name: "Michigan", lat: 43.326618, lng: -84.536095, abbrev: "MI" },
  { name: "Minnesota", lat: 45.694454, lng: -93.900192, abbrev: "MN" },
  { name: "Mississippi", lat: 32.741646, lng: -89.678696, abbrev: "MS" },
  { name: "Missouri", lat: 38.456085, lng: -92.288368, abbrev: "MO" },
  { name: "Montana", lat: 46.921925, lng: -110.454353, abbrev: "MT" },
  { name: "Nebraska", lat: 41.125370, lng: -98.268082, abbrev: "NE" },
  { name: "Nevada", lat: 38.313515, lng: -117.055374, abbrev: "NV" },
  { name: "New Hampshire", lat: 43.452492, lng: -71.563896, abbrev: "NH" },
  { name: "New Jersey", lat: 40.298904, lng: -74.521011, abbrev: "NJ" },
  { name: "New Mexico", lat: 34.840515, lng: -106.248482, abbrev: "NM" },
  { name: "New York", lat: 42.165726, lng: -74.948051, abbrev: "NY" },
  { name: "North Carolina", lat: 35.630066, lng: -79.806419, abbrev: "NC" },
  { name: "North Dakota", lat: 47.528912, lng: -99.784012, abbrev: "ND" },
  { name: "Ohio", lat: 40.388783, lng: -82.764915, abbrev: "OH" },
  { name: "Oklahoma", lat: 35.565342, lng: -96.928917, abbrev: "OK" },
  { name: "Oregon", lat: 44.572021, lng: -122.070938, abbrev: "OR" },
  { name: "Pennsylvania", lat: 40.590752, lng: -77.209755, abbrev: "PA" },
  { name: "Rhode Island", lat: 41.680893, lng: -71.511780, abbrev: "RI" },
  { name: "South Carolina", lat: 33.856892, lng: -80.945007, abbrev: "SC" },
  { name: "South Dakota", lat: 44.299782, lng: -99.438828, abbrev: "SD" },
  { name: "Tennessee", lat: 35.747845, lng: -86.692345, abbrev: "TN" },
  { name: "Texas", lat: 31.054487, lng: -97.563461, abbrev: "TX" },
  { name: "Utah", lat: 40.150032, lng: -111.862434, abbrev: "UT" },
  { name: "Vermont", lat: 44.045876, lng: -72.710686, abbrev: "VT" },
  { name: "Virginia", lat: 37.769337, lng: -78.169968, abbrev: "VA" },
  { name: "Washington", lat: 47.400902, lng: -121.490494, abbrev: "WA" },
  { name: "West Virginia", lat: 38.491226, lng: -80.954453, abbrev: "WV" },
  { name: "Wisconsin", lat: 44.268543, lng: -89.616508, abbrev: "WI" },
  { name: "Wyoming", lat: 42.755966, lng: -107.302490, abbrev: "WY" }
]
