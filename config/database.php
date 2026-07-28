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

    'default' => env('DB_CONNECTION', 'sqlite'),

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


        'global' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => false,
        ],

        'dynamic' => [
            'driver' => 'mysql',
            'host' => null,
            'port' => null,
            'database' => null,
            'username' => null,
            'password' => null,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => false,
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],





        // 'global' => [
        //     'driver' => 'mysql',
        //     'url' => env('DB_URL'),
        //     'host' => env('DB_HOST', '127.0.0.1'),
        //     'port' => env('DB_PORT', '3306'),
        //     'database' => env('DB_DATABASE', 'laravel'),
        //     'username' => env('DB_USERNAME', 'root'),
        //     'password' => env('DB_PASSWORD', ''),
        //     'unix_socket' => env('DB_SOCKET', ''),
        //     'charset' => env('DB_CHARSET', 'utf8mb4'),
        //     'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        //     'prefix' => '',
        //     'prefix_indexes' => true,
        //     'strict' => true,
        //     'engine' => null,
        //     'options' => extension_loaded('pdo_mysql') ? array_filter([
        //         PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        //     ]) : [],
        // ],

        // 'country' => [
        //     'driver' => 'mysql',
        //     'host' => env('DB_COUNTRY_HOST', '127.0.0.1'),
        //     'port' => env('DB_COUNTRY_PORT', '3306'),
        //     'database' => null,
        //     'username' => env('DB_COUNTRY_USERNAME', 'root'),
        //     'password' => env('DB_COUNTRY_PASSWORD', ''),
        //     'charset' => 'utf8mb4',
        //     'collation' => 'utf8mb4_unicode_ci',
        //     'prefix' => '',
        //     'strict' => true,
        // ],

    //     'mysql_sy' => [
    //     'driver' => 'mysql',
    //     'host' => env('DB_SY_HOST','127.0.0.1'),
    //     'database' => env('DB_SY_DATABASE','fleet_sy'),
    //     'username' => env('DB_SY_USERNAME','root'),
    //     'password' => env('DB_SY_PASSWORD',''),
    //     'charset' => 'utf8mb4',
    //     'collation' => 'utf8mb4_unicode_ci',
    //     'prefix' => '',
    //     'strict' => true,
    // ],

    // 'mysql_us' => [
    //     'driver' => 'mysql',
    //     'host' => env('DB_US_HOST','127.0.0.1'),
    //     'database' => env('DB_US_DATABASE','fleet_us'),
    //     'username' => env('DB_US_USERNAME','root'),
    //     'password' => env('DB_US_PASSWORD',''),
    //     'charset' => 'utf8mb4',
    //     'collation' => 'utf8mb4_unicode_ci',
    //     'prefix' => '',
    //     'strict' => true,
    // ],

    // 'mysql_qa' => [
    //     'driver' => 'mysql',
    //     'host' => env('DB_QA_HOST','127.0.0.1'),
    //     'database' => env('DB_QA_DATABASE',default: 'fleet_qa'),
    //     'username' => env('DB_QA_USERNAME','root'),
    //     'password' => env('DB_QA_PASSWORD',''),
    //     'charset' => 'utf8mb4',
    //     'collation' => 'utf8mb4_unicode_ci',
    //     'prefix' => '',
    //     'strict' => true,
    // ],

        'mariadb' => [
            'driver' => 'mariadb',
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

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
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

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'timeout' => (float) env('REDIS_TIMEOUT', 1.5),
            'read_write_timeout' => (float) env('REDIS_RW_TIMEOUT', 2),
        ],

        'notifications' => [
            'host' => env('REDIS_NOTIFICATIONS_HOST', '127.0.0.1'),
            'password' => env('REDIS_NOTIFICATIONS_PASSWORD', null),
            'port' => env('REDIS_NOTIFICATIONS_PORT', 6379),
            'database' => env('REDIS_NOTIFICATIONS_DB', 1),
            'timeout' => (float) env('REDIS_TIMEOUT', 1.5),
            'read_write_timeout' => (float) env('REDIS_RW_TIMEOUT', 2),
        ],

        'pubsub' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'timeout' => (float) env('REDIS_TIMEOUT', 1.5),
            'read_write_timeout' => 0,
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'timeout' => (float) env('REDIS_TIMEOUT', 1.5),
            'read_write_timeout' => (float) env('REDIS_RW_TIMEOUT', 2),
        ],

    ],

];
