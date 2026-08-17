<?php

use Core45\CookieConsent\Facades\CookieConsent;
use Core45\CookieConsent\Support\ConfigBuilder;

it('passes through arbitrary orestbida keys without a whitelist', function () {
    config()->set('cookieconsent.mode', 'opt-out');
    config()->set('cookieconsent.someFutureOption', ['nested' => true]);

    $built = app(ConfigBuilder::class)->build();

    expect($built['mode'])->toBe('opt-out')
        ->and($built['someFutureOption'])->toBe(['nested' => true])
        ->and($built['categories'])->toHaveKey('necessary');
});

it('strips package-only keys from the JS config', function () {
    $built = app(ConfigBuilder::class)->build();

    expect($built)->not->toHaveKeys(['translations_mode', 'csp_nonce', 'logging', 'iframemanager']);
});

it('preserves regex sentinel arrays untouched', function () {
    config()->set('cookieconsent.categories.analytics.autoClear.cookies', [
        ['name' => ['__regex__' => '^_ga', 'flags' => '']],
    ]);

    $built = app(ConfigBuilder::class)->build();

    expect($built['categories']['analytics']['autoClear']['cookies'][0]['name'])
        ->toBe(['__regex__' => '^_ga', 'flags' => '']);
});

it('builds a regex sentinel via the facade', function () {
    expect(CookieConsent::regex('^_ga'))->toBe(['__regex__' => '^_ga', 'flags' => ''])
        ->and(CookieConsent::regex('^_ga', 'i'))->toBe(['__regex__' => '^_ga', 'flags' => 'i']);
});

it('exposes the built config via the facade', function () {
    expect(CookieConsent::config())->toHaveKey('categories');
});

it('injects the active locale translations', function () {
    app()->setLocale('es');

    $built = app(ConfigBuilder::class)->build();

    expect($built['language']['default'])->toBe('es')
        ->and($built['language']['translations'])->toHaveKey('es')
        ->and($built['language']['translations']['es']['consentModal']['title'])->toBe('Utilizamos cookies')
        ->and($built['language']['translations'])->not->toHaveKey('en');
});

it('injects all published locales when translations_mode is all', function () {
    config()->set('cookieconsent.translations_mode', 'all');

    $built = app(ConfigBuilder::class)->build();

    expect(array_keys($built['language']['translations']))
        ->toEqualCanonicalizing(['bg', 'cs', 'de', 'el', 'en', 'es', 'et', 'fi', 'fr', 'hr', 'hu', 'it', 'lt', 'lv', 'nl', 'pl', 'pt', 'sk', 'sl', 'sq', 'sv', 'uk']);
});
