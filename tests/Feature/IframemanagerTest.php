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

    expect($html)
        ->toContain('vendor/iframemanager/iframemanager.js')
        ->toContain('im.run(imConfig)')
        ->toContain('Cargar vídeo')                    // localized loadBtn
        ->toContain('cc:onConsent')
        ->toContain('acceptService')
        ->not->toContain('"category"');                 // package-only key stripped
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
