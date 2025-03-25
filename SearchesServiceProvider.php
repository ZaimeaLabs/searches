<?php

declare(strict_types=1);

namespace ZaimeaLabs\Searches;

use Illuminate\Support\ServiceProvider;

class SearchesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('searches', function () {
            return new Searches;
        });
    }
}
