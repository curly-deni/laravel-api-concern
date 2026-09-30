<?php

namespace Aesis\ApiConcern;

use Illuminate\Support\ServiceProvider;

class ApiConcernServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'api-concern');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/api-concern'),
        ], 'api-concern-translations');
    }
}
