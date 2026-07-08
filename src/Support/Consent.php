<?php

namespace Core45\CookieConsent\Support;

use Illuminate\Support\Facades\Log;

class Consent
{
    protected static bool $warnedAboutLocalStorage = false;

    /** @return array<string, mixed>|null */
    public function raw(): ?array
    {
        if (config('cookieconsent.cookie.useLocalStorage', false)) {
            if (! static::$warnedAboutLocalStorage) {
                Log::warning('cookieconsent: cookie.useLocalStorage is enabled; server-side consent reads always report "no consent".');
                static::$warnedAboutLocalStorage = true;
            }

            return null;
        }

        $value = request()->cookie(config('cookieconsent.cookie.name', 'cc_cookie'));

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true) ?? json_decode(rawurldecode($value), true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return list<string> */
    public function categories(): array
    {
        return array_values(array_filter((array) ($this->raw()['categories'] ?? []), 'is_string'));
    }

    public function has(string $category): bool
    {
        return in_array($category, $this->categories(), true);
    }

    public function acceptType(): ?string
    {
        if ($this->raw() === null) {
            return null;
        }

        $accepted = $this->categories();
        $configured = (array) config('cookieconsent.categories', []);
        $readOnly = array_keys(array_filter($configured, fn (array $c): bool => ($c['readOnly'] ?? false) === true));

        if (array_diff(array_keys($configured), $accepted) === []) {
            return 'all';
        }

        if (array_diff($accepted, $readOnly) === []) {
            return 'necessary';
        }

        return 'custom';
    }
}
