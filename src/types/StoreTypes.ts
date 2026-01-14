export interface SnackbarType {
  visible: boolean;
  content: string;
  type: string;
  icon: string;
}
export interface ProfileType {
  name: string;
  email: string;
  sub: string;
  picture: string;
  given_name: string;
  role: string;
  [key: string]: any;
}
export interface PartnerType {
  id: number | null;
  name: string;
  website: string;
  logo: string;
  phone_number: string;
  brand_color: string;
  smtp_host: string;
  smtp_port: string;
  smtp_username: string;
  smtp_password: string;
  supported_brands: any[];
  is_active: number;
}
export interface Auth0UserType {
  user_id:   string,
  firstname: string,
  lastname:  string,
  username:  string,
  password:  string,
  role_id:   string,
}
export interface ManufacturerType {
  id: number | null;
  name: string;
  created_at: string;
  updated_at: string;
}
export interface UccFilingFiltersType {
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  provider_id: string | null;
  assignee_id: string | null;
  ucc_status: string | null;
  buyer_state: string | null;
  equipment_min: number | null;
  equipment_max: number | null;
}
export interface UccBuyersFiltersType {
  search:   string|null,
  state:    string|null,
  industry: string|null,
}
export interface UccBuyersType {
  id: number | null;
  partner_id: string | null;
  buyer_company: string | null;
  buyer_adress1: string | null;
  buyer_adress2: string | null;
  buyer_city: string | null;
  buyer_state: string | null;
  buyer_zip: string | null;
  buyer_phone: string | null;
  buyer_website: string | null;
  buyer_fax: string | null;
  buyer_fips: string | null;
  buyer_county: string | null;
  buyer_sic: string | null;
  buyer_sic_desc: string | null;
  buyer_duns: string | null;
  created_at: string | null;
  updated_at: string | null;
}

/**
 * TYPES FOR GLOBAL STATE
 **/
export interface GlobalStateTypes {
  snackbar: SnackbarType;
  profile: ProfileType | null;
  sidebar: boolean;
  is_mobile: boolean;
  global_loading: boolean;
  is_data_loaded: boolean;

  // Modals
  modals: any;

  // For UCC Viewer
  view_ucc_id: any;

  // Theming
  backend_theme: string;
  frontend_theme: string;

  // Dialog
  dialog: any;

  // Partner Form
  partner_loading: boolean;
  partner_modal: boolean;
  partner_form: PartnerType;

  // Auth0 Form
  new_user_modal: boolean;
  user_added_modal: boolean;

  // Auth0 Data
  auth0_users: any[];
  auth0_roles: any[];

  // Identities
  idp_partners: PartnerType[];
  idp_partner_users: any[];

  // Ucc Explorer Data
  ucc_statuses: any[]; // for filters
  ucc_assignees: any[];
  ucc_buyers: any[];
  ucc_contacts: any[];
  ucc_equipments: any[];
  ucc_files: any[];
  ucc_filings: any[];
  ucc_providers: any[];
  ucc_map_columns: any[];

  // Catalog Server (Only for brands)
  cat_manufacturers: ManufacturerType[],

  // UCC Filing Filters
  ucc_filing_filters: any;

  // UCC Buyers Filters
  ucc_buyers_filters: any;

  // Table Search Input
  table_search: "",
}
