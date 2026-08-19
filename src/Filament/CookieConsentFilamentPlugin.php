<?php

namespace Core45\CookieConsent\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the read-only consent-log resource on a Filament panel.
 *
 * Supports Filament v3, v4 and v5 from the same call: which resource class gets
 * registered is decided by FilamentVersion at register() time, so the host app
 * never has to name a version. See FilamentVersion for why the branch cannot be
 * a runtime feature check.
 */
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
        $panel->resources([FilamentVersion::resourceClass()]);
    }

    public function boot(Panel $panel): void {}
}
