import {defineStore} from 'pinia';
import {UccServer} from "@/plugins/ucc-server";
import type {ProfileType} from '@/types/StoreTypes';
import type {GlobalStateTypes} from '@/types/StoreTypes';

export const GlobalStore = defineStore('globals', {
  state: (): GlobalStateTypes => ({
    snackbar: {
      visible: false,
      content: '',
      type: '',
      icon: ''
    },
    profile: null,
    sidebar: false,
    is_mobile: false,
    global_loading: false,
    is_data_loaded: false,

    // Modals
    modals: {
      ucc_filing_viewer: false,
      contact_form: false,
      customer_profile: false,
    },

    // For UCC Viewer
    view_ucc_id: null,

    // Theming
    backend_theme: 'dark',
    frontend_theme: 'dark',

    // Dialog
    dialog: {
      show: false,
      title: "",
      content: "",
      ok_txt: 'OK',
      cancel_txt: 'Cancel',
      resolver: null as null | ((v: boolean) => void),
    },

    // Partner Form
    partner_loading: false,
    partner_modal: false,
    partner_form: {
      id: null,
      name: '',
      website: '',
      logo: '',
      phone_number: '',
      brand_color: '',
      smtp_host: '',
      smtp_port: '',
      smtp_username: '',
      smtp_password: '',
      supported_brands: [],
      is_active: 1,
    },

    // Auth0 Form
    new_user_modal: false,
    user_added_modal: false,

    // Auth0 Data
    auth0_users: [],
    auth0_roles: [],

    // Identities
    idp_partners: [],
    idp_partner_users: [],

    // Ucc Explorer Data
    ucc_statuses: [], // for filters
    ucc_assignees: [],
    ucc_buyers: [],
    ucc_contacts: [],
    ucc_equipments: [],
    ucc_files: [],
    ucc_filings: [],
    ucc_providers: [],
    ucc_map_columns: [],

    // Catalog Server (Only for brands)
    cat_manufacturers: [],

    // UCC Filing Filters
    ucc_filing_filters: {
      search:         null,
      start_date:     "",
      end_date:       "",
      provider_id:    null,
      assignee_id:    null,
      ucc_status:     null,
      buyer_state:    null,
      equipment_min:  null,
      equipment_max:  null,
    },

    // UCC Buyers Filters
    ucc_buyers_filters: {
      search:   null,
      city:     null,
      state:    null,
      industry: null,
    },

    // Table Search Input
    table_search: "",
  }),
  persist: {
    pick: [
      'profile',
      'sidebar',
      'backend_theme',
      'frontend_theme',

      // Auth0
      'users',
      'partners',
      'auth0_roles',

      // Filters
      'ucc_filing_filters'
    ],
  },
  actions: {
    // UI Actions
    LogError(message: string) {
      console.log('%c'+message, 'color: #FF2F50; font-weight: bold;');
    },
    ShowError(message: string) {
      this.snackbar = {
        visible: true,
        content: message,
        type: 'error',
        icon: 'mdi-alert-circle'
      };
    },
    LogSuccess(message: string) {
      console.log('%c'+message, 'color: #4CAF50; font-weight: bold;');
    },
    ShowSuccess(message: string) {
      this.snackbar = {
        visible: true,
        content: message,
        type: 'success',
        icon: 'mdi-check-circle'
      };
    },
    CloseSnackbar() {
      this.snackbar = {
        visible: false,
        content: "",
        type: '',
        icon: ''
      };
    },
    SetProfile(profile: ProfileType | null) {
      this.profile = profile;
    },
    SetState(payload: Partial<GlobalStateTypes>) {
      Object.assign(this, payload)
    },

    // API Calls
    async FetchUsers(token:any) {
      return await UccServer(token).get('/users/fetch').then(response => {
        this.auth0_users = response.data;
      });
    },
    async FetchPartners(token:any) {
      return await UccServer(token).get('/partners/fetch').then(response => {
        this.idp_partners = response.data;
      });
    },
    async FetchPartnerUsers(token:any) {
      return await UccServer(token).get('/partnerusers/fetch').then(response => {
        this.idp_partner_users = response.data;
      });
    },
    async FetchFiles(token:any) {
      return await UccServer(token).get(`/files/fetch/${this.FindPartnerId()}`).then(response => {
        this.ucc_files = response.data;
      });
    },
    async SubmitPartnerForm(token:any,my_user_id:string) {
      const form = new FormData();
      const route = this.partner_form.id ? "/partners/update" : "/partners/store";
      this.partner_loading = true;
      form.append("user_id", my_user_id);
      form.append("partner", JSON.stringify(this.partner_form));
      UccServer(token).post(route, form).then((res: any) => {
        console.log(res.data);
        this.ShowSuccess(`Form successfully ${this.partner_form.id ? 'updated!' : 'added!'}`);
      }).finally(() => {
        this.partner_modal = false;
        this.FetchPartners(token).finally(() => {
          this.partner_loading = false;
        });
      });
    },

    // Fetches all data reduce overhead
    async FetchAllData(token:string) {
      return await UccServer(token).get(`/data/fetch/${this.FindPartnerId()}`).then(res=>{
        this.auth0_users       = res.data.auth0_users;
        this.auth0_roles       = res.data.auth0_roles;

        this.idp_partners      = res.data.idp_partners;
        this.idp_partner_users = res.data.idp_partner_users;
        this.cat_manufacturers = res.data.cat_manufacturers;

        this.ucc_files         = res.data.ucc_files;
        this.ucc_statuses      = res.data.ucc_statuses;
        this.ucc_map_columns   = res.data.ucc_map_columns;
        this.ucc_assignees     = res.data.ucc_assignees;
        this.ucc_providers     = res.data.ucc_providers;
        this.ucc_contacts      = res.data.ucc_contacts;

        this.is_data_loaded    = true;
      });
    },

    // Dialogs
    OpenDialog(title: string, content: string, ok_txt: string = 'OK', cancel_txt: string = 'CANCEL'): Promise<boolean> {
      this.dialog.title = title;
      this.dialog.content = content;
      this.dialog.show = true;
      this.dialog.cancel_txt = cancel_txt;
      this.dialog.ok_txt = ok_txt;
      return new Promise(resolve => {
        this.dialog.resolver = resolve;
      })
    },
    ConfirmDialog() {
      this.dialog.show = false
      this.dialog.resolver?.(true)
      this.dialog.resolver = null
    },
    CancelDialog() {
      this.dialog.show = false
      this.dialog.resolver?.(false)
      this.dialog.resolver = null
    },

    // For recovering Partner ID
    FindPartnerId() {
      const find_pu = this.idp_partner_users.find((p:any) => p.user_id == this.profile?.sub);
      return find_pu ? find_pu.partner_id : null;
    }
  }
});
