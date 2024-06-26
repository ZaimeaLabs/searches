<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PDO;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\ZaimeaLabs\Searches\SearchesServiceProvider::class];
    }

    public function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        $this->initDatabase();
    }

    protected function initDatabase($prefix = '')
    {
        DB::purge('mysql');

        $this->app['config']->set('database.connections.mysql', [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'db_test'),
            'username'       => env('DB_USERNAME', 'db_test'),
            'password'       => env('DB_PASSWORD', 'secret'),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => $prefix,
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);

        DB::setDefaultConnection('mysql');

        $this->artisan('migrate:fresh', ['--path' => __DIR__ . '/Fixtures/database/migrations/create_tables.php', '--realpath' => true]);

    }
}
