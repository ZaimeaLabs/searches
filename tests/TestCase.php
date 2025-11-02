<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PDO;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\Zaimea\Searches\SearchesServiceProvider::class];
    }

    public function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        $this->initDatabase();

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('video_id')->nullable();
                $table->string('title');
                $table->date('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('post_id');
                $table->string('body');
                $table->date('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('videos')) {
            Schema::create('videos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->date('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('blogs')) {
            Schema::create('blogs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->string('subtitle');
                $table->string('body');

                $table->fullText('title');
                $table->fullText(['title', 'subtitle']);
                $table->fullText(['title', 'subtitle', 'body']);

                $table->unsignedInteger('video_id')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('body')->nullable();

                $table->fullText('title');
                $table->fullText(['title', 'subtitle']);
                $table->fullText(['title', 'subtitle', 'body']);

                $table->unsignedInteger('video_id')->nullable();

                $table->timestamps();
            });
        }
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
    }

    protected function tearDown(): void
    {
        // Drop tables to ensure clean state between tests (SQLite in-memory gets cleared,
        // but for safety, drop if exists)
        Schema::dropIfExists('posts');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('pages');

        parent::tearDown();
    }
}
