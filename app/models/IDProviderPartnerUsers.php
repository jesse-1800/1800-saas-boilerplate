<?php namespace App\Models;
use Kernel\Database\Connection;
use Kernel\Database\QueryBuilder as Model;

class IDProviderPartnerUsers extends Model
{
    // We're using multiple-database, WE MUST INSTANTIATE the class
    // before we can use the static calls e.g. Model::get()
    protected static $table = "idp_partner_users";
    public function __construct($data = null)
    {
        parent::__construct($data);
        Connection::initialize('identities');
    }
}
