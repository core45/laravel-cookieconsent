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
        $this->app->singleton(CookieConsentManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cookieconsent.php' => config_path('cookieconsent.php'),
        ], 'cookieconsent-config');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cookieconsent');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/cookieconsent'),
        ], 'cookieconsent-lang');

        $this->publishes([
            __DIR__.'/../resources/dist/cookieconsent' => public_path('vendor/cookieconsent'),
            __DIR__.'/../resources/dist/iframemanager' => public_path('vendor/iframemanager'),
        ], 'cookieconsent-assets');
    }
}
