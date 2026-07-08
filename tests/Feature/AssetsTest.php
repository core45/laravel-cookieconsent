<?php

use Core45\CookieConsent\CookieConsentServiceProvider;
use Illuminate\Support\ServiceProvider;

it('ships non-empty vendored assets', function (string $path) {
    $file = __DIR__.'/../../resources/dist/'.$path;

    expect(file_exists($file))->toBeTrue()
        ->and(filesize($file))->toBeGreaterThan(1000);
})->with([
    'cookieconsent/cookieconsent.umd.js',
    'cookieconsent/cookieconsent.css',
    'iframemanager/iframemanager.js',
    'iframemanager/iframemanager.css',
]);

it('registers the asset publish tag', function () {
    $paths = ServiceProvider::pathsToPublish(
        CookieConsentServiceProvider::class,
        'cookieconsent-assets'
    );

    expect($paths)->not->toBeEmpty();
});
