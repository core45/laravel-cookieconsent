<?php

namespace Core45\CookieConsent\Tests;

/**
 * Boots the app with `cookieconsent.logging.csrf` already false, so
 * CookieConsentServiceProvider::boot() registers the `cookie-consent/log`
 * route WITHOUT ValidateCsrfToken from the start.
 *
 * A regular test body can't exercise this via `config()->set(...)`: Testbench
 * rebuilds the app (re-running the provider's boot()) before every test, so a
 * runtime override made inside a test is always too late to influence how the
 * route was registered. `defineEnvironment()` runs before boot, which is why
 * this needs its own TestCase rather than a `config()->set()` call inside an
 * `it()` block — see the "is rate limited" test in
 * tests/Feature/LogEndpointTest.php for the same documented Testbench
 * constraint applied to a different config key.
 */
class CsrfDisabledTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('cookieconsent.logging.csrf', false);
    }
}
