<?php

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
