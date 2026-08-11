<?php

use Illuminate\Support\Facades\Blade;

it('emits a blocked external script with data-src', function () {
    $html = Blade::render("@cookiescript('analytics', 'https://example.com/ga.js', ['data-service' => 'Google Analytics'])");

    expect($html)->toBe('<script type="text/plain" data-category="analytics" data-service="Google Analytics" data-src="https://example.com/ga.js"></script>');
});

it('emits a blocked inline script block', function () {
    $html = Blade::render("@cookiescript('analytics')console.log(1);@endcookiescript");

    expect($html)->toBe('<script type="text/plain" data-category="analytics">console.log(1);</script>');
});

it('supports the negated category form', function () {
    $html = Blade::render("@cookiescript('!analytics')cleanup();@endcookiescript");

    expect($html)->toContain('data-category="!analytics"');
});

it('escapes attribute values', function () {
    $html = Blade::render("@cookiescript('analytics', 'https://x.test/a.js', ['data-service' => '\"><script>'])");

    expect($html)->not->toContain('"><script>');
});

it('renders a preferences trigger with the translated default label', function () {
    $html = Blade::render('@cookiepreferences');

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal">Manage preferences</button>');
});

it('accepts a custom label and extra attributes on the preferences trigger', function () {
    $html = Blade::render("@cookiepreferences('Cookie settings', ['class' => 'btn btn-link', 'aria-label' => 'Open cookie settings'])");

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal" class="btn btn-link" aria-label="Open cookie settings">Cookie settings</button>');
});

it('renders boolean attributes bare and drops false ones', function () {
    $html = Blade::render("@cookiepreferences('Settings', ['disabled' => true, 'hidden' => false])");

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal" disabled>Settings</button>');
});

it('escapes the preferences trigger label and attributes', function () {
    $html = Blade::render("@cookiepreferences('\"><script>x</script>', ['class' => '\"><script>'])");

    expect($html)->not->toContain('<script>');
});

it('ignores attempts to override the preferences trigger hook attributes', function () {
    $html = Blade::render("@cookiepreferences('Settings', ['data-cc' => 'accept-all', 'type' => 'submit'])");

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal">Settings</button>');
});

it('does not let a trailing newline smuggle a reserved attribute name past the filter', function () {
    $html = Blade::render("@cookiepreferences('Settings', [\"data-cc\\n\" => 'accept-all', \"type\\n\" => 'submit'])");

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal">Settings</button>');
});

it('drops inline event handlers and malformed attribute names', function () {
    $html = Blade::render("@cookiepreferences('Settings', ['onclick' => 'x()', 'on click' => 'y()', 'ONMOUSEOVER' => 'z()'])");

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal">Settings</button>');
});

it('follows the active locale for the default preferences trigger label', function () {
    app()->setLocale('es');

    $html = Blade::render('@cookiepreferences');

    expect($html)->toContain('>Gestionar preferencias</button>');
});

it('falls back to a plain label when the translation line is absent', function () {
    // A locale the package ships no lines for, with the fallback pointed at it
    // too, so nothing can resolve the key.
    config(['app.fallback_locale' => 'zz']);
    app()->setLocale('zz');

    $html = Blade::render('@cookiepreferences');

    expect($html)->toBe('<button type="button" data-cc="show-preferencesModal">Manage preferences</button>');
});
