<?php

use Core45\CookieConsent\Filament\CookieConsentFilamentPlugin;
use Core45\CookieConsent\Filament\Resources\ConsentLogResource;
use Filament\Panel;

it('registers the resource on a panel via the plugin', function () {
    $panel = Panel::make()->id('testing');

    CookieConsentFilamentPlugin::make()->register($panel);

    expect($panel->getResources())->toContain(ConsentLogResource::class);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('is a read-only resource', function () {
    expect(ConsentLogResource::canCreate())->toBeFalse();
})->skip(! class_exists(Panel::class), 'Filament is not installed.');
