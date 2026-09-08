<?php

use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Simulates `vendor:publish --tag=cookieconsent-lang` followed by a consumer
 * trimming the published file: an extra lang path holding a two-section
 * cookieconsent.php with no acceptNecessaryBtn, and an iframemanager.php
 * with no loadAllBtn.
 */
beforeEach(function () {
    $this->langDir = sys_get_temp_dir().'/cookieconsent-lang-'.uniqid();
    File::ensureDirectoryExists($this->langDir.'/vendor/cookieconsent/en');

    File::put($this->langDir.'/vendor/cookieconsent/en/cookieconsent.php', <<<'PHP'
    <?php

    return [
        'consentModal' => [
            'title' => 'Cookies',
            'description' => 'Only the essentials.',
            'acceptAllBtn' => 'OK',
        ],
        'preferencesModal' => [
            'title' => 'Preferences',
            'savePreferencesBtn' => 'Save',
            'sections' => [
                ['title' => 'Intro', 'description' => 'Why.'],
                ['title' => 'Necessary', 'description' => 'Always on.', 'linkedCategory' => 'necessary'],
            ],
        ],
    ];
    PHP);

    File::put($this->langDir.'/vendor/cookieconsent/en/iframemanager.php', <<<'PHP'
    <?php

    return [
        'youtube' => [
            'notice' => 'Third-party video.',
            'loadBtn' => 'Load it',
        ],
    ];
    PHP);

    app('translator')->addPath($this->langDir);
    app()->setLocale('en');
});

afterEach(function () {
    File::deleteDirectory($this->langDir);
});

function builtEnglish(): array
{
    return app(ConfigBuilder::class)->build()['language']['translations']['en'];
}

it('merges a published file over the package file by default', function () {
    $lines = builtEnglish();

    expect(config('cookieconsent.translations_override'))->toBe('merge')
        ->and($lines['consentModal']['title'])->toBe('Cookies')
        ->and($lines['consentModal']['acceptNecessaryBtn'])->toBe('Reject all')
        ->and($lines['preferencesModal']['sections'])->toHaveCount(3);
});

it('uses the published file wholesale in replace mode', function () {
    config()->set('cookieconsent.translations_override', 'replace');

    $lines = builtEnglish();

    expect($lines['consentModal']['title'])->toBe('Cookies')
        ->and($lines['consentModal'])->not->toHaveKey('acceptNecessaryBtn')
        ->and($lines['preferencesModal']['sections'])->toHaveCount(2)
        ->and(array_column($lines['preferencesModal']['sections'], 'linkedCategory'))->toBe(['necessary']);
});

it('falls back to the package file for locales without a published file', function () {
    config()->set('cookieconsent.translations_override', 'replace');
    app()->setLocale('es');

    $lines = app(ConfigBuilder::class)->build()['language']['translations']['es'];

    expect($lines['consentModal']['title'])->toBe('Utilizamos cookies')
        ->and($lines['preferencesModal']['sections'])->toHaveCount(3);
});

it('falls back to the app fallback locale like the translator does', function () {
    config()->set('cookieconsent.translations_override', 'replace');
    config()->set('app.fallback_locale', 'en');
    app()->setLocale('zz');

    $translations = app(ConfigBuilder::class)->build()['language']['translations'];

    expect($translations)->toHaveKey('zz')
        ->and($translations['zz']['consentModal']['title'])->toBe('Cookies');
});

it('never exposes translations_override to the JS config', function () {
    config()->set('cookieconsent.translations_override', 'replace');

    expect(app(ConfigBuilder::class)->build())->not->toHaveKey('translations_override');
});

it('applies replace mode to the iframemanager strings as well', function () {
    config()->set('cookieconsent.iframemanager', [
        'enabled' => true,
        'services' => [
            'youtube' => ['category' => 'analytics', 'embedUrl' => 'https://www.youtube-nocookie.com/embed/{data-id}'],
        ],
    ]);
    Route::middleware('web')->get('/_im', fn () => Blade::render('@iframemanager'));

    $merged = $this->get('/_im')->getContent();

    config()->set('cookieconsent.translations_override', 'replace');
    $replaced = $this->get('/_im')->getContent();

    expect($merged)->toContain('Load it')->toContain('loadAllBtn')
        ->and($replaced)->toContain('Load it')->not->toContain('loadAllBtn');
});
