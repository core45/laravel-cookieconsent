{{-- Laravel CookieConsent Guidelines for AI Code Assistants --}}
{{-- Source: https://github.com/core45/laravel-cookieconsent --}}
{{-- License: MIT | (c) core45 --}}

## Cookie Consent

- `core45/laravel-cookieconsent` wraps orestbida/cookieconsent v3 with PHP-first config, lang-file i18n, server-side consent reads, Blade script gating, iframemanager click-to-load embeds, and a database consent audit trail.
- Always activate the `cookieconsent-development` skill when working with cookie banners, consent categories, GDPR script gating, the `@cookieconsent`/`@cookiescript`/`@consent`/`@iframe` Blade directives, the `CookieConsent` facade, the `consent:*` middleware, or the `consent_logs` audit trail.
