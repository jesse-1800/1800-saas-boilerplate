<?php

use App\Models\UccBuyers;
use Auth as Authenticator;
use App\Models\UccFilings;
use App\Models\UccContacts;
use App\Models\UccAssignees;
use App\Models\UccProviders;
use App\Models\UccEquipments;
use App\Models\UccFileManager;
use App\Models\IDProviderPartners;
use App\Models\CatalogManufacturers;
use App\Models\IDProviderPartnerUsers;

class DataController {
    /**
     * Initial data fetch on App.vue mount.
     */
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new Authenticator();
        $auth->isLoggedIn();
        $path = "{$_SERVER['DOCUMENT_ROOT']}/app/config/mapping";
        $data = array(
            'auth0_roles'       => $auth->fetch_roles(),
            'auth0_users'       => $auth->fetch_users(true,true),

            // Shared databases
            'idp_partners'      => (new IDProviderPartners)::fetch(),
            'idp_partner_users' => (new IDProviderPartnerUsers)::get(),
            'cat_manufacturers' => (new CatalogManufacturers)::fetch(),

            // UCC Data
            'ucc_files'         => UccFileManager::where('partner_id',$partner_id)->get(),
            'ucc_map_columns'   => json_decode(file_get_contents("$path/ucc_filings.json")),
            'ucc_statuses'      => UccFilings::distinct('ucc_status')->get(),
            'ucc_assignees'     => UccAssignees::select('id,assignee_company')->where('partner_id',$partner_id)->get(),
            'ucc_providers'     => UccProviders::select('id,provider_company')->where('partner_id',$partner_id)->get(),
        );
        return json($data);
    }

    public function map_data($partner_id)
    {
        $states = UccBuyers::select('distinct buyer_state as state')
            ->where('partner_id',$partner_id)
            ->get();

        foreach($states as $state) {
            // Loop each state and add its count
            $state->count = UccBuyers::where('buyer_state',$state->state)
                ->where('partner_id',$partner_id)
                ->count();

            // Bind the city to its state
            $state->cities = UccBuyers::select('distinct buyer_city as city')
                ->where('buyer_state',$state->state)
                ->where('partner_id',$partner_id)
                ->get();

            // Loop each city and add its count
            foreach ($state->cities as $city) {
                $city->count = UccBuyers::where('buyer_city',$city->city)
                    ->where('buyer_state',$state->state)
                    ->where('partner_id',$partner_id)
                    ->count();
            }
        }

        return json($states);
    }

    /**
     * This is used by UccFilingsViewer.vue
     */
    public function find_ucc_data($ucc_filing_id)
    {
        $ucc_filing = UccFilings::find($ucc_filing_id);
        $buyer      = UccBuyers::find($ucc_filing->buyer_id);
        $provider   = UccProviders::find($ucc_filing->provider_id);
        $assignee   = UccAssignees::find($ucc_filing->assignee_id);
        $data = (object) [
            "ucc_filing" => $ucc_filing->data,
            "buyer"      => $buyer->data,
            "contacts"   => UccContacts::where('buyer_id',$buyer->id)->get(),
            "provider"   => $provider ? $provider->data : null,
            "assignee"   => $assignee ? $assignee->data : null,
            "equipments" => UccEquipments::where('ucc_filing_id',$ucc_filing->id)->get(),
        ];
        return json($data);
    }
}