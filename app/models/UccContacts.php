<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class UccFilings
 *
 * @package App\Models
 */
class UccContacts extends Model
{
    protected static $table = "ucc_contacts";

    public static function get_or_insert($buyer_id,$partner_id, $firstname, $lastname, $title, $email, $phone , $address, $city, $state, $zip)
    {
        if (empty($buyer_id) || empty($firstname)) {
            return null;
        }

        # Check if exists, then return id
        $finder = self::where('partner_id',$partner_id)
            ->where('buyer_id',$buyer_id)
            ->where('firstname',$firstname)
            ->where('lastname',$lastname)
            ->first();
        if ($finder) return $finder->id;

        # Otherwise, insert a new contact
        $is_inserted = self::insert([
            'partner_id' => $partner_id,
            'buyer_id'   => $buyer_id,
            'firstname'  => $firstname,
            'lastname'   => $lastname,
            'title'      => $title,
            'email'      => $email,
            'phone'      => $phone,
            'address'    => $address,
            'city'       => $city,
            'state'      => $state,
            'zip'        => $zip,
        ]);
        return $is_inserted ? self::lastInsertedId() : null;
    }
}