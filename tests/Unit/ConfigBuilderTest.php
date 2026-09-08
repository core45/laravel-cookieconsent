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

it('drops preferences sections linked to a category that is not configured', function () {
    config()->set('cookieconsent.categories', [
        'necessary' => ['enabled' => true, 'readOnly' => true],
    ]);

    $sections = app(ConfigBuilder::class)->build()['language']['translations']['en']['preferencesModal']['sections'];

    expect($sections)->toHaveCount(2)
        ->and(array_is_list($sections))->toBeTrue()
        ->and($sections[0])->not->toHaveKey('linkedCategory')
        ->and($sections[1]['linkedCategory'])->toBe('necessary')
        ->and(array_column($sections, 'linkedCategory'))->not->toContain('analytics');
});

it('keeps every section when all linked categories are configured', function () {
    $sections = app(ConfigBuilder::class)->build()['language']['translations']['en']['preferencesModal']['sections'];

    expect($sections)->toHaveCount(3)
        ->and(array_column($sections, 'linkedCategory'))->toBe(['necessary', 'analytics']);
});

it('drops orphan sections in every embedded locale', function () {
    config()->set('cookieconsent.translations_mode', 'all');
    config()->set('cookieconsent.categories', [
        'necessary' => ['enabled' => true, 'readOnly' => true],
    ]);

    $translations = app(ConfigBuilder::class)->build()['language']['translations'];

    foreach ($translations as $locale => $lines) {
        expect(array_column($lines['preferencesModal']['sections'], 'linkedCategory'))
            ->toBe(['necessary'], "locale {$locale} still carries an orphan section");
    }
});

it('drops orphan sections supplied through inline language.translations config', function () {
    config()->set('cookieconsent.language.translations.en.preferencesModal.sections', [
        3 => ['title' => 'Marketing', 'description' => 'Ads.', 'linkedCategory' => 'marketing'],
    ]);

    $sections = app(ConfigBuilder::class)->build()['language']['translations']['en']['preferencesModal']['sections'];

    expect(array_column($sections, 'linkedCategory'))->toBe(['necessary', 'analytics'])
        ->and(array_is_list($sections))->toBeTrue();
});

it('keeps only unlinked sections when no categories are configured', function () {
    config()->set('cookieconsent.categories', []);

    $sections = app(ConfigBuilder::class)->build()['language']['translations']['en']['preferencesModal']['sections'];

    expect($sections)->toHaveCount(1)
        ->and($sections[0])->not->toHaveKey('linkedCategory');
});

it('leaves malformed preferencesModal structures untouched', function () {
    config()->set('cookieconsent.language.translations.en.preferencesModal', 'not-an-array');
    config()->set('cookieconsent.language.translations.fr', 'not-an-array');

    $translations = app(ConfigBuilder::class)->build()['language']['translations'];

    expect($translations['en']['preferencesModal'])->toBe('not-an-array')
        ->and($translations['fr'])->toBe('not-an-array');
});

it('filters orphan sections before the policy hash is computed', function () {
    $builder = app(ConfigBuilder::class);
    $base = $builder->policyHash();

    config()->set('cookieconsent.language.translations.en.preferencesModal.sections', [
        3 => ['title' => 'Marketing', 'description' => 'Ads.', 'linkedCategory' => 'marketing'],
    ]);

    expect($builder->policyHash())->toBe($base);
});
