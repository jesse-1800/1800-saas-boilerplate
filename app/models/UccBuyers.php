<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class UccFilings
 *
 * @package App\Models
 */
class UccBuyers extends Model
{
    protected static $table = "ucc_buyers";

    public static function get_or_insert($partner_id,$buyer_data,$index)
    {
        $buyer_data->buyer_id = "GEN-".time()."-".$index;
        # We are forcing to create a new record to avoid
        # having one partner edit another partner's buyer profile.
        $is_inserted = self::insert([
            'id'             => $buyer_data->buyer_id,
            'partner_id'     => $partner_id,
            'buyer_company'  => $buyer_data->buyer_company  ?? "",
            'buyer_adress1'  => $buyer_data->buyer_adress1  ?? "",
            'buyer_adress2'  => $buyer_data->buyer_adress2  ?? "",
            'buyer_city'     => $buyer_data->buyer_city     ?? "",
            'buyer_state'    => $buyer_data->buyer_state    ?? "",
            'buyer_zip'      => $buyer_data->buyer_zip      ?? "",
            'buyer_phone'    => $buyer_data->buyer_phone    ?? "",
            'buyer_website'  => $buyer_data->buyer_website  ?? "",
            'buyer_fax'      => $buyer_data->buyer_fax      ?? "",
            'buyer_fips'     => $buyer_data->buyer_fips     ?? "",
            'buyer_county'   => $buyer_data->buyer_county   ?? "",
            'buyer_sic'      => $buyer_data->buyer_sic      ?? "",
            'buyer_sic_desc' => $buyer_data->buyer_sic_desc ?? "",
            'buyer_duns'     => $buyer_data->buyer_duns     ?? "",
        ]);

        return $is_inserted ? $buyer_data->buyer_id : false;
    }
}