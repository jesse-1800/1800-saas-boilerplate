<?php

/**
 * Database Configuration
 */
use Kernel\Database\Connection;


/**
 * Here you can specify multiple database
 * connections by giving an alias.
 *
 * argument 1 is the alias for the connection instance
 * argument 2 is an array of connection parameters
 */

# Development
if (SYSTEM_MODE == 'development') {
    /*Connection::parameters('default',[
        'driver'   => 'mysql',
        'hostname' => 'localhost',
        'database' => '1800-ucc-explorer',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8',
        'port'     => 3306
    ]);*/

}

# Production
else {
    /*Connection::parameters('default', [
        'driver' => 'mysql',
        'hostname' => 'localhost',
        'database' => '1800-ucc-explorer',
        'username' => 'root',
        'password' => '8800Doral$',
        'charset' => 'utf8',
        'port' => 3306
    ]);*/
}


/**
 * Begin Transaction with database
 */
Connection::initialize('default');