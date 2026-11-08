<?php

use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

function renderScripts(): string
{
    Route::middleware('web')->get('/_page', fn () => Blade::render('@cookieconsent'));

    return test()->get('/_page')->getContent();
}

it('renders assets, revived config, and the run call in order', function () {
    $html = renderScripts();

    expect($html)
        ->toContain('vendor/cookieconsent/cookieconsent.css')
        ->toContain('vendor/cookieconsent/cookieconsent.umd.js')
        ->toContain('__regex__')          // reviver present
        ->toContain('CookieConsent.run(config)');

    // config declaration comes before the run call
    expect(strpos($html, 'let config'))->toBeLessThan(strpos($html, 'CookieConsent.run(config)'));
});

it('renders the pre-run stack between config and run', function () {
    Route::middleware('web')->get('/_stacked', fn () => Blade::render(
        "@push('cookieconsent:config')<script>config.cookie.expiresAfterDays = () => 365;</script>@endpush\n@cookieconsent"
    ));

    $html = test()->get('/_stacked')->getContent();
    $mutation = strpos($html, 'expiresAfterDays = () => 365');

    expect($mutation)->toBeGreaterThan(strpos($html, 'let config'))
        ->toBeLessThan(strpos($html, 'CookieConsent.run(config)'));
});

it('embeds the CSRF token and logging listeners when logging is enabled', function () {
    $html = renderScripts();

    expect($html)->toContain('cc:onFirstConsent')->toContain('cc:onChange')->toContain('X-CSRF-TOKEN');
});

it('omits logging listeners when logging is disabled', function () {
    config()->set('cookieconsent.logging.enabled', false);

    expect(renderScripts())->not->toContain('cc:onFirstConsent');
});

it('emits nonce attributes when csp_nonce is configured', function () {
    config()->set('cookieconsent.csp_nonce', fn () => 'test-nonce-123');

    expect(renderScripts())->toContain('nonce="test-nonce-123"');
});

it('escapes config safely via Js::from', function () {
    config()->set('cookieconsent.categories.necessary.evil', '</script><script>alert(1)');

    expect(renderScripts())->not->toContain('</script><script>alert(1)');
});

it('renders exactly the 3.3.1 markup while defer_assets is off', function () {
    config()->set('cookieconsent.csp_nonce', fn () => 'fixture-nonce');

    $html = renderScripts();
    $html = str_replace(session()->token(), 'CSRF_TOKEN', $html);

    expect($html)->toBe(file_get_contents(__DIR__.'/../Fixtures/scripts-v3.3.1.html'));
});

it('loads the library with defer behind an inline stub when defer_assets is on', function () {
    config()->set('cookieconsent.defer_assets', true);

    $html = renderScripts();
    $stub = strpos($html, 'window.CookieConsent = stub');
    $library = strpos($html, 'defer src="http://localhost/vendor/cookieconsent/cookieconsent.umd.js"');

    expect($stub)->toBeInt()
        ->and($library)->toBeInt()
        ->and($stub)->toBeLessThan($library)
        ->and($library)->toBeLessThan(strpos($html, 'let config'))
        ->and(strpos($html, 'deferred.flush()'))->toBeLessThan(strpos($html, 'CookieConsent.run(config)'));

    expect($html)->toContain('href="http://localhost/vendor/cookieconsent/cookieconsent.css" media="print" id="cookieconsent-stylesheet"')
        ->not->toMatch('/<script[^>]*(?<!defer) src=/')
        ->not->toMatch('/<link[^>]*cookieconsent\.css"\s*>/');
});

it('puts the nonce on every script tag when defer_assets is on', function () {
    config()->set('cookieconsent.defer_assets', true);
    config()->set('cookieconsent.csp_nonce', fn () => 'test-nonce-123');

    $html = renderScripts();

    expect(substr_count($html, '<script'))->toBe(substr_count($html, '<script nonce="test-nonce-123"'))
        ->and(substr_count($html, '<script'))->toBe(4)
        ->and($html)->not->toMatch('/\son[a-z]+="/');
});

it('keeps defer_assets out of the config passed to CookieConsent.run()', function () {
    config()->set('cookieconsent.defer_assets', true);

    expect(app(ConfigBuilder::class)->build())->not->toHaveKey('defer_assets');
});
