<?php

namespace Core45\CookieConsent\Filament;

use Core45\CookieConsent\Filament\Resources\ConsentLogResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class CookieConsentFilamentPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'core45-cookieconsent';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ConsentLogResource::class]);
    }

    public function boot(Panel $panel): void {}
}
