# core45/laravel-cookieconsent

A Laravel wrapper for [orestbida/cookieconsent v3](https://cookieconsent.orestbida.com/) and its companion
[iframemanager](https://github.com/orestbida/iframemanager). It gives you a PHP-first configuration file instead of
a hand-written JS blob, i18n through normal Laravel lang files, server-side reads of what the visitor consented to,
Blade-driven script gating, and an append-only database audit trail of every consent decision.

| Capability | What you get |
|---|---|
| Configuration | A single `config/cookieconsent.php` array, passed through to `CookieConsent.run()` almost verbatim |
| Translations | Standard Laravel lang files (`resources/lang/{locale}/cookieconsent.php`), `active` or `all` locales |
| Script gating | `@cookiescript` Blade directives that emit blocked `<script type="text/plain">` tags |
| Server-side reads | `@consent`/`Consent`/`CookieConsent` facade + a `consent:*` route middleware |
| Audit trail | An append-only `consent_logs` table, export/prune Artisan commands, optional Filament resource |
| Media consent | An `@iframemanager` / `@iframe()` integration for YouTube-style click-to-load embeds |
| Security | Optional CSP nonce injection for every script tag the package renders |

## Installation

```bash
composer require core45/laravel-cookieconsent

php artisan vendor:publish --tag=cookieconsent-config
php artisan vendor:publish --tag=cookieconsent-assets
php artisan migrate
```

Other publishable tags, used only if you need to customize them:

```bash
php artisan vendor:publish --tag=cookieconsent-lang        # resources/lang/vendor/cookieconsent/{locale}/*.php
php artisan vendor:publish --tag=cookieconsent-views        # resources/views/vendor/cookieconsent/*.blade.php
php artisan vendor:publish --tag=cookieconsent-migrations   # copy the migration into database/migrations
```

Add `@cookieconsent` to your layout's `<head>`. This is the recommended placement — the banner script waits for
`DOMContentLoaded` internally, so it's safe to load it before the rest of the page:

```blade
<head>
    ...
    @cookieconsent
</head>
```

The package auto-registers its consent cookie (`cookieconsent.cookie.name`, default `cc_cookie`) as an exception to
Laravel's `EncryptCookies` middleware on boot. You do not need to edit `bootstrap/app.php` yourself.

## Configuration

`config/cookieconsent.php` is published as a plain PHP array. Every key **except** `translations_mode`, `csp_nonce`,
`logging`, and `iframemanager` is passed straight through to `CookieConsent.run()` — see the
[configuration reference](https://cookieconsent.orestbida.com/reference/configuration-reference.html) for every
option orestbida/cookieconsent understands.

```php
return [

    // Passed through to CookieConsent.run() — see the configuration reference linked above.
    'cookie' => [
        'name' => 'cc_cookie',
        'expiresAfterDays' => 182,
        // WARNING: enabling `useLocalStorage` disables server-side consent
        // reads (@consent, Consent::has(), consent:* middleware). See below.
    ],

    'mode' => 'opt-in',
    'revision' => 0,

    'guiOptions' => [
        'consentModal' => ['layout' => 'box', 'position' => 'bottom left'],
        'preferencesModal' => ['layout' => 'box'],
    ],

    'categories' => [
        'necessary' => ['enabled' => true, 'readOnly' => true],
        'analytics' => [
            'autoClear' => [
                'cookies' => [
                    // Use CookieConsent::regex('^_ga') for RegExp matching.
                    ['name' => '_gid'],
                ],
            ],
        ],
    ],

    // Package-only keys, stripped before the array reaches the JS config:
    'translations_mode' => 'active', // 'active' = current locale only, 'all' = every published locale
    'csp_nonce' => null,             // see "Content Security Policy" below
    'logging' => [ /* ... */ ],      // see "Consent audit trail" below
    'iframemanager' => [ /* ... */ ], // see "iframemanager" below
];
```

### RegExp cookie matching

`autoClear.cookies[].name` normally does an exact string match. To emit a real JS `RegExp` instead, use the
`CookieConsent::regex()` helper — it returns a sentinel array that the package's Blade view revives into
`new RegExp(...)` client-side:

```php
use Core45\CookieConsent\Facades\CookieConsent;

'autoClear' => [
    'cookies' => [
        ['name' => CookieConsent::regex('^_ga')],       // matches _ga, _ga_ABC123, ...
        ['name' => CookieConsent::regex('^_gid$', 'i')], // second arg is JS RegExp flags
    ],
],
```

A plain string (`['name' => '_gid']`) is always an exact match.

## Translations

Publish the language files and edit them like any other Laravel translation:

```bash
php artisan vendor:publish --tag=cookieconsent-lang
```

```
resources/lang/vendor/cookieconsent/en/cookieconsent.php
resources/lang/vendor/cookieconsent/es/cookieconsent.php
resources/lang/vendor/cookieconsent/en/iframemanager.php
resources/lang/vendor/cookieconsent/es/iframemanager.php
```

The package ships `en` and `es` out of the box. `cookieconsent.php` maps directly onto orestbida's
`language.translations.{locale}` shape (`consentModal`, `preferencesModal`, etc); `iframemanager.php` supplies the
per-service `loadBtn`/`notice`/... strings.

`config('cookieconsent.translations_mode')` controls how many locales are sent to the browser:

- `'active'` (default) — only the current `app()->getLocale()` locale is embedded.
- `'all'` — every published locale directory is embedded, so client-side locale switches (`CookieConsent.setLanguage()`) work without a page reload.

## Script gating

`@cookiescript` blocks a `<script>` tag until the visitor has consented to its category, using orestbida's
`type="text/plain" data-category="..."` convention.

External script:

```blade
@cookiescript('analytics', 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX')
```

Inline script:

```blade
@cookiescript('analytics')
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-XXXXXXX');
@endcookiescript
```

You can also negate a category to run cleanup code when consent is *not* given (or is withdrawn), and pass extra
HTML attributes as a third argument:

```blade
@cookiescript('!analytics')
    // runs when analytics consent is absent
@endcookiescript

@cookiescript('analytics', 'https://example.com/ga.js', ['data-service' => 'Google Analytics'])
```

> **Blade quirk:** don't glue `@endcookiescript` directly onto a word character with no whitespace or punctuation
> between them — Blade's compiler won't recognize `someCode@endcookiescript` as a directive boundary. This is
> rarely an issue in practice because inline scripts almost always end in `;`, `}`, or a newline before the closing
> directive, as in the examples above.

## Server-side consent

Read what the visitor has consented to from PHP, without waiting for JavaScript:

```blade
@consent('analytics')
    <p>Analytics-only content.</p>
@endconsent
```

```php
use Core45\CookieConsent\Support\Consent;
use Core45\CookieConsent\Facades\CookieConsent;

app(Consent::class)->has('analytics');   // bool
CookieConsent::has('analytics');         // same, via the facade
CookieConsent::categories();             // list<string>, e.g. ['necessary', 'analytics']
CookieConsent::acceptType();             // 'all' | 'necessary' | 'custom' | null (no cookie yet)
```

Gate a whole route behind a category with the `consent:*` middleware — it aborts with `403` when consent is missing:

```php
Route::get('/dashboard/analytics', AnalyticsController::class)
    ->middleware('consent:analytics');
```

> **`useLocalStorage` warning.** If you set `cookie.useLocalStorage` to `true` in the config, orestbida/cookieconsent
> stores consent in the browser's `localStorage` instead of a cookie. That value never reaches the server, so
> `@consent`/`@endconsent`, `Consent::has()`, `CookieConsent::has()`, and the `consent:*` middleware will **always**
> report "no consent" — they degrade silently to `false`/`[]`/`null`, except for a **one-time** `Log::warning()`
> the first time a read is attempted (guarded by a static flag, so it fires once per PHP process — once per
> request under classic PHP-FPM, once total under Octane). Leave `useLocalStorage` off (the default) if you rely
> on any server-side consent check.

## Consent audit trail

Every accept/change interaction the banner fires (`cc:onFirstConsent`, `cc:onChange`) is POSTed to an internal
`cookieconsent.log` route and written as a new, immutable row in `consent_logs`. The table is append-only: the
`ConsentLog` model throws if you try to update an existing row.

| Column | Contents |
|---|---|
| `consent_id`, `revision` | orestbida's own consent identifier and config revision |
| `action` | `first_consent` or `change` |
| `accept_type` | `all`, `custom`, or `necessary` |
| `accepted_categories`, `rejected_categories`, `accepted_services` | JSON arrays |
| `language_code` | locale active on the banner at the time of consent |
| `policy_version`, `policy_hash` | your configured version string, and a canonicalized sha256 of the effective config — see `CookieConsent::policyHash()` |
| `ip_address`, `user_agent` | evidentiary metadata, subject to the PII config below |
| `user_type`, `user_id` | polymorphic link to the authenticated user, if any |
| `payload` | the full raw cookie payload, for future-proofing |

Evidentiary fields (`ip_address`, `user_agent`, `user_type`/`user_id`, `policy_hash`) are always derived server-side
from the request — they are never trusted from client input.

### PII configuration

```php
'logging' => [
    'enabled' => true,
    'csrf' => true,
    'capture_ip' => 'raw',            // 'raw' | 'hashed' | false
    'ip_hash_salt' => env('COOKIECONSENT_IP_SALT'),
    'capture_user_agent' => true,
    'link_user' => true,
    'morph_id_type' => 'int',         // 'int' | 'uuid' | 'ulid' | 'string' — must match your User key type
    'policy_version' => null,
    'retention_days' => null,         // null disables pruning
    'rate_limit' => '30,1',           // Laravel throttle expression applied to the log endpoint
],
```

> **Legal note.** Storing an IP address and/or user agent alongside a consent decision is itself an act of
> processing personal data, even though the purpose is to *prove* consent. Document this processing activity in
> your own privacy policy, and tune `capture_ip` (`'hashed'` or `false`) and `retention_days` to match your data
> minimization obligations under GDPR/LOPD-GDD or the regime that applies to you. This package stores evidence;
> it does not decide your retention policy for you.

> **Security note.** `capture_ip => 'raw'`/`'hashed'` relies on `$request->ip()`, whose accuracy depends on your
> application's `trustProxies` configuration (`bootstrap/app.php` → `->withMiddleware()->trustProxies(...)`, or
> `App\Http\Middleware\TrustProxies` on older skeletons). Behind a load balancer, reverse proxy, or CDN, an
> unconfigured trusted-proxy list means every request is logged with the proxy's IP, not the visitor's.

### Retrieval

```bash
php artisan cookieconsent:export --format=csv > consent-evidence.csv
php artisan cookieconsent:export --format=json --user=42
php artisan cookieconsent:export --consent-id=abc123 --from=2026-01-01 --to=2026-06-30
```

The underlying `ConsentLog` model exposes matching Eloquent scopes:

```php
ConsentLog::forConsentId('abc123')->get();
ConsentLog::forUser($user)->get();
ConsentLog::between('2026-01-01', '2026-06-30')->get();
ConsentLog::latestPerConsentId()->get(); // one row per consent_id, the most recent
```

### Retention

```bash
php artisan cookieconsent:prune
```

Deletes rows older than `logging.retention_days`. It's a no-op (logs an info message, deletes nothing) when
`retention_days` is `null`. Schedule it if you want automatic pruning:

```php
// routes/console.php
Schedule::command('cookieconsent:prune')->daily();
```

### Filament plugin

An optional, read-only `ConsentLogResource` is available if `filament/filament` (`^5.0`) is installed. Register it
on any panel:

```php
use Core45\CookieConsent\Filament\CookieConsentFilamentPlugin;

$panel->plugin(CookieConsentFilamentPlugin::make());
```

The resource has no create/edit pages — consent logs are evidence, not editable records.

## iframemanager

Gate third-party embeds (YouTube, Vimeo, maps, ...) behind a consent category with a click-to-load placeholder,
using [iframemanager](https://github.com/orestbida/iframemanager).

```php
'iframemanager' => [
    'enabled' => true,
    'services' => [
        'youtube' => [
            'category' => 'analytics', // which cookieconsent category unlocks this service
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/{data-id}',
            'thumbnailUrl' => 'https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg',
            'iframe' => [
                'allow' => 'accelerometer; autoplay; encrypted-media',
            ],
        ],
    ],
],
```

Place `@iframemanager` near `@cookieconsent` (typically right after it):

```blade
@cookieconsent
@iframemanager
```

Then drop a placeholder wherever you'd normally embed the iframe:

```blade
@iframe('youtube', $videoId, ['params' => 'rel=0', 'thumbnail' => $customThumbnailUrl])
```

Per-service copy (`loadBtn`, `notice`, ...) comes from `resources/lang/{locale}/iframemanager.php`, keyed by
service name (`youtube` in the example above), just like the main `cookieconsent.php` lang file.

## Advanced JS / custom callbacks

Two ways to hook into the banner's behavior beyond what the config array exposes:

**1. `cc:*` window events** — orestbida/cookieconsent dispatches `cc:onFirstConsent`, `cc:onConsent`, `cc:onChange`,
`cc:onModalReady`, `cc:onModalShow`, and `cc:onModalHide` on `window`. Listen to them from any script on the page,
inline or bundled:

```html
<script>
window.addEventListener('cc:onConsent', () => {
    if (window.CookieConsent.acceptedCategory('analytics')) {
        // ...
    }
});
</script>
```

**2. The `cookieconsent:config` push stack** — runs after the revived `config` JS variable is declared but
*before* `CookieConsent.run(config)` is called, so you can mutate `config` in place (e.g. make a static option
dynamic):

```blade
@push('cookieconsent:config')
<script>
    config.cookie.expiresAfterDays = () => 365;
</script>
@endpush
```

### Google Consent Mode recipe

Combining both mechanisms to wire up [Google Consent Mode v2](https://developers.google.com/tag-platform/security/guides/consent):

```blade
@push('cookieconsent:config')
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('consent', 'default', { analytics_storage: 'denied', ad_storage: 'denied' });
    window.addEventListener('cc:onConsent', () => {
        if (window.CookieConsent.acceptedCategory('analytics')) {
            gtag('consent', 'update', { analytics_storage: 'granted' });
        }
    });
</script>
@endpush
```

## Content Security Policy

If your app runs under a strict CSP, set `csp_nonce` to a callable that returns the per-request nonce:

```php
'csp_nonce' => fn () => \Illuminate\Support\Facades\Vite::cspNonce(),
```

Every inline and external `<script>` tag **the package itself renders** (`@cookieconsent`, `@iframemanager`) gets a
`nonce="..."` attribute automatically.

> **CSP caveat.** Scripts *you* push via `@push('cookieconsent:config')` are plain Blade/HTML — they do **not**
> automatically inherit the configured nonce. Under a strict CSP you must add `nonce="..."` to your own pushed
> `<script>` tags yourself, e.g. `<script nonce="{{ $nonce }}">...</script>`.

## Versioning & License

This package follows [Semantic Versioning](https://semver.org/). It is open-sourced under the
[MIT license](LICENSE.md), Copyright (c) 2026 core45.
