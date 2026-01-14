<?php namespace App\Models;

use Google\Service\Eventarc\Provider;
use Kernel\Database\QueryBuilder as Model;

/**
 * Class UccFilings
 *
 * @package App\Models
 */
class UccProviders extends Model
{
    protected static $table = "ucc_providers";

    public static function get_or_insert($partner_id, $provider)
    {
        # Return ID if exists
        $finder = self::where('id',$provider->provider_id)->first();
        if ($finder) return $finder->id;

        # Otherwise, insert
        self::insert([
            'id'              => $provider->provider_id,
            'partner_id'      => $partner_id,
            'provider_class'  => $provider->provider_class,
            'provider_company'=> $provider->provider_company,
            'provider_city'   => $provider->provider_city,
            'provider_state'  => $provider->provider_state,
        ]);
        return $provider->provider_id;
    }
}