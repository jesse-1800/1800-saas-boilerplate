<?php namespace App\Models;

use Dotenv\Dotenv;

class Environment {
    public function __construct()
    {
        $envFile = ".env.".SYSTEM_MODE;
        $path = "{$_SERVER['DOCUMENT_ROOT']}";

        if (!file_exists("$path/$envFile")) {
            return json([
                'result' => false,
                'message' => "Environment file $envFile not found in $path"
            ]);
        }
        $dotenv = Dotenv::createImmutable($path, $envFile);
        $dotenv->load();
    }

    public function get($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
