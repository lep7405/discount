<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        env('DB_CONNECTION_APP_1') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_1', '127.0.0.1'),
            'port' => env('DB_PORT_APP_1', '3306'),
            'app_name' => env('NAME_APP_1'),
            'app_link' => env('LINK_APP_1'),
            'database' => env('DB_DATABASE_APP_1', 'forge'),
            'username' => env('DB_USERNAME_APP_1', 'forge'),
            'password' => env('DB_PASSWORD_APP_1', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        env('DB_CONNECTION_APP_2') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_2', '127.0.0.1'),
            'port' => env('DB_PORT_APP_2', '3306'),
            'app_name' => env('NAME_APP_2'),
            'app_link' => env('LINK_APP_2'),
            'database' => env('DB_DATABASE_APP_2', 'forge'),
            'username' => env('DB_USERNAME_APP_2', 'forge'),
            'password' => env('DB_PASSWORD_APP_2', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        env('DB_CONNECTION_APP_2') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_2', '127.0.0.1'),
            'port' => env('DB_PORT_APP_2', '3306'),
            'app_name' => env('NAME_APP_2'),
            'app_link' => env('LINK_APP_2'),
            'database' => env('DB_DATABASE_APP_2', 'forge'),
            'username' => env('DB_USERNAME_APP_2', 'forge'),
            'password' => env('DB_PASSWORD_APP_2', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        env('DB_CONNECTION_APP_3') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_3', '127.0.0.1'),
            'port' => env('DB_PORT_APP_3', '3306'),
            'app_name' => env('NAME_APP_3'),
            'app_link' => env('LINK_APP_3'),
            'database' => env('DB_DATABASE_APP_3', 'forge'),
            'username' => env('DB_USERNAME_APP_3', 'forge'),
            'password' => env('DB_PASSWORD_APP_3', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        env('DB_CONNECTION_APP_4') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_4', '127.0.0.1'),
            'port' => env('DB_PORT_APP_4', '3306'),
            'app_name' => env('NAME_APP_4'),
            'app_link' => env('LINK_APP_4'),
            'database' => env('DB_DATABASE_APP_4', 'forge'),
            'username' => env('DB_USERNAME_APP_4', 'forge'),
            'password' => env('DB_PASSWORD_APP_4', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        env('DB_CONNECTION_APP_5') => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_APP_5', '127.0.0.1'),
            'port' => env('DB_PORT_APP_5', '3306'),
            'app_name' => env('NAME_APP_5'),
            'app_link' => env('LINK_APP_5'),
            'database' => env('DB_DATABASE_APP_5', 'forge'),
            'username' => env('DB_USERNAME_APP_5', 'forge'),
            'password' => env('DB_PASSWORD_APP_5', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
