<?php

use Core45\CookieConsent\Support\Consent;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

function withConsentCookie(?string $value): Consent
{
    $request = Request::create('/', 'GET');

    if ($value !== null) {
        $request->cookies->set(config('cookieconsent.cookie.name', 'cc_cookie'), $value);
    }

    app()->instance('request', $request);

    return app(Consent::class);
}

it('excludes the consent cookie from encryption', function () {
    expect(app(EncryptCookies::class)->isDisabled('cc_cookie'))->toBeTrue();
});

it('returns false/empty/null without a cookie', function () {
    $consent = withConsentCookie(null);

    expect($consent->has('analytics'))->toBeFalse()
        ->and($consent->categories())->toBe([])
        ->and($consent->acceptType())->toBeNull();
});

it('reads categories from a valid cookie', function () {
    $consent = withConsentCookie(json_encode(['categories' => ['necessary', 'analytics']]));

    expect($consent->has('analytics'))->toBeTrue()
        ->and($consent->has('marketing'))->toBeFalse()
        ->and($consent->categories())->toBe(['necessary', 'analytics']);
});

it('handles a malformed cookie gracefully', function () {
    expect(withConsentCookie('{not json')->has('analytics'))->toBeFalse();
});

it('derives acceptType from configured categories', function () {
    expect(withConsentCookie(json_encode(['categories' => ['necessary', 'analytics']]))->acceptType())->toBe('all')
        ->and(withConsentCookie(json_encode(['categories' => ['necessary']]))->acceptType())->toBe('necessary');

    config()->set('cookieconsent.categories.marketing', []);

    expect(withConsentCookie(json_encode(['categories' => ['necessary', 'analytics']]))->acceptType())->toBe('custom');
});

it('degrades with a one-time warning under useLocalStorage', function () {
    config()->set('cookieconsent.cookie.useLocalStorage', true);
    Log::shouldReceive('warning')->once();

    $consent = withConsentCookie(json_encode(['categories' => ['analytics']]));

    expect($consent->has('analytics'))->toBeFalse()
        ->and($consent->categories())->toBe([])
        ->and($consent->acceptType())->toBeNull();
});
