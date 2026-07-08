<?php

namespace Core45\CookieConsent;

use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Support\ServiceProvider;

class CookieConsentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cookieconsent.php', 'cookieconsent');
        $this->app->singleton(ConfigBuilder::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cookieconsent.php' => config_path('cookieconsent.php'),
        ], 'cookieconsent-config');
    }
}
