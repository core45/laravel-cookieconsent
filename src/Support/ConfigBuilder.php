<?php

namespace Core45\CookieConsent\Support;

use Illuminate\Support\Arr;

class ConfigBuilder
{
    /** @var list<string> Package-only keys never passed to the JS config. */
    public const PACKAGE_KEYS = ['translations_mode', 'csp_nonce', 'logging', 'iframemanager'];

    /** @return array<string, mixed> */
    public function build(): array
    {
        return Arr::except((array) config('cookieconsent', []), self::PACKAGE_KEYS);
    }
}
