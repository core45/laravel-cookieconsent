<?php

// This file lives outside tests/Feature on purpose: it needs
// `cookieconsent.logging.csrf` set to false *before* the app boots (see
// Core45\CookieConsent\Tests\CsrfDisabledTestCase), which requires a
// different base TestCase than the rest of the Feature suite. Pest only
// allows one TestCase class per matched path, so it can't share
// tests/Feature's directory-wide `uses()` rule — see tests/Pest.php.
//
// This asserts through the real middleware resolver rather than making an
// HTTP round trip: under Testbench, `PreventRequestForgery::handle()`
// bypasses itself unconditionally when `runningInConsole() &&
// runningUnitTests()` are both true (always true under Pest), and the
// minimal Testbench app doesn't even define a `web` middleware group with
// real middleware in it — so a POST-and-assert-201 test would pass whether
// or not the fix exists. Resolving the route's exclusion list against the
// actual CSRF middleware class is what genuinely proves the exclusion is
// effective.

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

it('registers the log route with an excluded-middleware list', function () {
    $route = Route::getRoutes()->getByName('cookieconsent.log');

    // Sanity check: proves defineEnvironment's csrf=false override actually
    // reached CookieConsentServiceProvider::boot() before route registration.
    expect($route->excludedMiddleware())->not->toBe([]);
});

it('excludes CSRF validation from the log route when logging.csrf is disabled', function () {
    $route = Route::getRoutes()->getByName('cookieconsent.log');

    $resolved = app('router')->resolveMiddleware(
        [PreventRequestForgery::class],
        $route->excludedMiddleware(),
    );

    expect($resolved)->not->toContain(PreventRequestForgery::class);
});
