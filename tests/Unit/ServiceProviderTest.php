<?php

use Core45\CookieConsent\CookieConsentServiceProvider;

it('registers the service provider', function () {
    expect(app()->providerIsLoaded(CookieConsentServiceProvider::class))->toBeTrue();
});
