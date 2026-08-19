<?php

use Core45\CookieConsent\Filament\ConsentLogFormatter;
use Core45\CookieConsent\Filament\FilamentVersion;

/**
 * Version-neutral: every test here runs whether Filament is absent, v3, or v4+.
 * Nothing in this file may reference a Filament class directly — naming one
 * that the installed major does not ship is a fatal at class-load time, which
 * is the exact failure mode FilamentVersion exists to prevent.
 */
it('reports whether Filament is installed', function () {
    expect(FilamentVersion::isInstalled())->toBe(class_exists('Filament\Panel'));
});

it('detects the schemas API introduced in v4', function () {
    expect(FilamentVersion::usesSchemas())->toBe(class_exists('Filament\Schemas\Schema'));
});

it('returns null for the major when Filament is absent', function () {
    expect(FilamentVersion::major())->toBeNull()
        ->and(FilamentVersion::version())->toBeNull();
})->skip(FilamentVersion::isInstalled(), 'Filament is installed.');

it('reports a supported major when Filament is installed', function () {
    expect(FilamentVersion::major())->toBeIn([3, 4, 5]);
})->skip(! FilamentVersion::isInstalled(), 'Filament is not installed.');

it('agrees with the schemas probe about the major', function () {
    expect(FilamentVersion::major() >= 4)->toBe(FilamentVersion::usesSchemas());
})->skip(! FilamentVersion::isInstalled(), 'Filament is not installed.');

it('picks the resource matching the installed major', function () {
    $expected = FilamentVersion::usesSchemas()
        ? 'Core45\CookieConsent\Filament\Resources\ConsentLogResource'
        : 'Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource';

    expect(FilamentVersion::resourceClass())->toBe($expected);
});

it('shares the accepted-services formatter across majors', function () {
    expect(ConsentLogFormatter::acceptedServices(['necessary' => [], 'analytics' => ['ga4']]))
        ->toBe('necessary: None · analytics: ga4')
        ->and(ConsentLogFormatter::acceptedServices([]))->toBe('—')
        ->and(ConsentLogFormatter::acceptedServices('not-an-array'))->toBe('—');
});

it('shares the payload formatter across majors', function () {
    $formatted = ConsentLogFormatter::payload(['categories' => ['necessary'], 'url' => 'https://example.com/a']);

    expect($formatted)->toContain("\n    ")
        ->and($formatted)->toContain('https://example.com/a');
});

it('decodes a json string payload before pretty-printing it', function () {
    expect(ConsentLogFormatter::payload('{"a":1}'))->toBe("{\n    \"a\": 1\n}");
});

it('reports the Filament status through the console command', function () {
    // One expectation per invocation, deliberately. Chaining expectsOutputToContain
    // is not portable across the Laravel versions this package supports: 13.26
    // matches each expectation against one successive line of output, so only the
    // first in a chain can pass, while 13.25 searched the whole buffer.
    $expected = FilamentVersion::isInstalled()
        ? [
            'v'.FilamentVersion::major(),
            FilamentVersion::usesSchemas() ? 'schemas (v4+)' : 'infolists (v3)',
            FilamentVersion::resourceClass(),
        ]
        : ['Filament is not installed'];

    foreach ($expected as $fragment) {
        $this->artisan('cookieconsent:filament')
            ->expectsOutputToContain($fragment)
            ->assertSuccessful();
    }
});
