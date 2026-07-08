<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config()->set('cookieconsent.iframemanager', [
        'enabled' => true,
        'services' => [
            'youtube' => [
                'category' => 'analytics',
                'embedUrl' => 'https://www.youtube-nocookie.com/embed/{data-id}',
                'thumbnailUrl' => 'https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg',
            ],
        ],
    ]);
});

it('renders im.run with localized services and CookieConsent glue', function () {
    app()->setLocale('es');
    Route::middleware('web')->get('/_im', fn () => Blade::render('@iframemanager'));

    $html = $this->get('/_im')->getContent();

    // Js::from() hex-escapes double quotes (JSON_HEX_QUOT), so a literal
    // '"category"' never appears in the rendered output either way —
    // asserting its absence alone is hollow. If the `category` key ever
    // leaked into imConfig, it would render as this escaped form instead
    // (verified empirically against Js::from(['category' => 'x'])). Built
    // via chr(92) rather than typed as """ so the backslash survives
    // untouched through this source file.
    $escapedCategoryKey = chr(92).'u0022category'.chr(92).'u0022';

    expect($html)
        ->toContain('vendor/iframemanager/iframemanager.js')
        ->toContain('im.run(imConfig)')
        ->toContain('Cargar vídeo')                    // localized loadBtn
        ->toContain('cc:onConsent')
        ->toContain('acceptService')
        ->not->toContain($escapedCategoryKey)           // package-only key stripped from imConfig
        ->toContain('categoryMap')
        ->toContain('analytics');                       // category value still present, via categoryMap
});

it('renders nothing when disabled', function () {
    config()->set('cookieconsent.iframemanager.enabled', false);
    Route::middleware('web')->get('/_im_off', fn () => Blade::render('@iframemanager'));

    expect(trim($this->get('/_im_off')->getContent()))->toBe('');
});

it('emits iframe placeholder divs', function () {
    $html = Blade::render("@iframe('youtube', 'dQw4w9WgXcQ', ['params' => 'rel=0'])");

    expect($html)->toBe('<div data-service="youtube" data-id="dQw4w9WgXcQ" data-params="rel=0"></div>');
});

it('escapes iframe attributes', function () {
    $html = Blade::render("@iframe('youtube', '\"><script>')");

    expect($html)->not->toContain('"><script>');
});
