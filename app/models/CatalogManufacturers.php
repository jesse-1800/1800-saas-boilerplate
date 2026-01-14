<?php namespace App\Models;
use Kernel\Database\Connection;
use Kernel\Database\QueryBuilder as Model;
class CatalogManufacturers extends Model
{
    // We're using multiple-database, WE MUST INSTANTIATE the class
    // before we can use the static calls e.g. Model::get()
    protected static $table = "manufacturers";
    public function __construct($data = null)
    {
        parent::__construct($data);
        Connection::initialize('catalog');
    }

    public static function fetch()
    {
        # Fetch data first
        $data = self::get();

        # Revert the connection to default asap.
        Connection::initialize('default');

        return $data;
    }
}
