<?php

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Publish assets into Testbench's public path so the browser can load them.
    $this->artisan('vendor:publish', ['--tag' => 'cookieconsent-assets', '--force' => true]);

    // CookieConsent.js refuses to render for automated browsers by default
    // (it checks navigator.webdriver). Disable that guard for this E2E run.
    config(['cookieconsent.hideFromBots' => false]);

    // @cookieconsent can render in <head>: CookieConsent.js mounts its
    // #cc-main container onto document.body, but the rendered script defers
    // CookieConsent.run() to DOMContentLoaded, so it's safe to place the
    // directive before <body> is parsed.
    Route::middleware('web')->get('/demo', function () {
        return Blade::render(<<<'BLADE'
            <!doctype html>
            <html lang="en">
            <head><title>Demo</title>@cookieconsent</head>
            <body>
                <h1>Demo page</h1>
                @cookiescript('analytics')document.title = 'analytics-ran';@endcookiescript
            </body>
            </html>
        BLADE);
    });
});

it('shows the banner, accepts, hides it, and writes a consent log', function () {
    $page = visit('/demo');

    $page->assertSee('We use cookies')
        ->click('Accept all')
        ->assertDontSee('We use cookies');

    expect(ConsentLog::count())->toBe(1)
        ->and(ConsentLog::sole()->accepted_categories)->toContain('analytics');
});

it('keeps gated scripts inert until consent', function () {
    $page = visit('/demo');

    // pest-plugin-browser v4.3 has no assertTitleIsNot(); assert the known
    // pre-consent title instead of the absence of the post-consent one.
    $page->assertTitle('Demo');

    $page->click('Accept all');

    $page->assertTitle('analytics-ran');
});
