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
