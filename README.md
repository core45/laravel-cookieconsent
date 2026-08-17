# core45/laravel-cookieconsent

A Laravel wrapper for [orestbida/cookieconsent v3](https://cookieconsent.orestbida.com/) and its companion
[iframemanager](https://github.com/orestbida/iframemanager). It gives you a PHP-first configuration file instead of
a hand-written JS blob, i18n through normal Laravel lang files, server-side reads of what the visitor consented to,
Blade-driven script gating, and a database audit trail of every consent decision whose model write path is
append-only.

The package bundles orestbida/cookieconsent **3.1.0** and iframemanager **1.3.0**, served self-hosted from
`resources/dist` / the published `vendor/cookieconsent` assets — no CDN request is made. Upstream updates are
re-vendored and checksum-verified by the maintainers, then shipped as a new package release; there is no
auto-update mechanism, so pin the package version if you need a specific upstream JS version.

| Capability | What you get |
|---|---|
| Configuration | A single `config/cookieconsent.php` array, passed through to `CookieConsent.run()` almost verbatim |
| Translations | Standard Laravel lang files (`lang/vendor/cookieconsent/{locale}/cookieconsent.php`), `active` or `all` locales |
| Script gating | `@cookiescript` Blade directives that emit blocked `<script type="text/plain">` tags |
| Server-side reads | `@consent`/`Consent`/`CookieConsent` facade + a `consent:*` route middleware |
| Audit trail | A `consent_logs` table with an append-only model write path, export/prune Artisan commands, optional Filament resource |
| Media consent | An `@iframemanager` / `@iframe()` integration for YouTube-style click-to-load embeds |
| Security | Optional CSP nonce injection for every script tag the package renders |

## Installation

Requires **PHP 8.4+** and **Laravel 12 or 13**.

Laravel 11 support was dropped in 3.0: every `laravel/framework` 11.x release is now covered by unfixed
security advisories, so Composer refuses to install it by default. On PHP 8.2/8.3 use `^1.1`; on Laravel 11
use `^2.0`, understanding that the framework underneath it is out of security support.

```bash
composer require core45/laravel-cookieconsent

php artisan vendor:publish --tag=cookieconsent-config
php artisan vendor:publish --tag=cookieconsent-assets
php artisan migrate
```

Other publishable tags, used only if you need to customize them:

```bash
php artisan vendor:publish --tag=cookieconsent-lang        # lang/vendor/cookieconsent/{locale}/*.php
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
lang/vendor/cookieconsent/{locale}/cookieconsent.php
lang/vendor/cookieconsent/{locale}/iframemanager.php
```

The package ships `bg`, `cs`, `de`, `el`, `en`, `es`, `et`, `fi`, `fr`, `hr`, `hu`, `it`, `lt`, `lv`, `nl`, `pl`, `pt`,
`sk`, `sl`, `sq`, `sv` and `uk` out of the box. `cookieconsent.php`
maps directly onto orestbida's `language.translations.{locale}` shape (`consentModal`, `preferencesModal`, etc);
`iframemanager.php` supplies the per-service `loadBtn`/`notice`/... strings.

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

## Letting visitors change their mind

Once the banner has been answered it never shows again on its own, so the site must provide a way back into the
preferences modal — GDPR requires that consent be as easy to withdraw as it is to give. `@cookiepreferences`
renders that trigger:

```blade
@cookiepreferences
```

It emits a button carrying orestbida's `data-cc="show-preferencesModal"` hook:

```html
<button type="button" data-cc="show-preferencesModal">Manage preferences</button>
```

The default label is the `consentModal.showPreferencesBtn` translation line, so it follows the current locale with
no extra strings to publish (falling back to `Manage preferences` if you've removed that line — sites offering only
accept-all/reject-all in the banner do). Pass your own label and any HTML attributes to style it:

```blade
@cookiepreferences('Cookie settings', ['class' => 'footer__link', 'aria-label' => 'Open cookie settings'])
```

Attribute values are escaped; `true` renders a bare attribute and `false`/`null` omits it. `data-cc` and `type` are
owned by the directive and cannot be overridden, so the trigger can't be silently detached from the modal. Inline
`on*` event handlers are dropped — the nonce-based CSP this package supports would block them anyway.

Put it wherever your privacy links live — footer, cookie policy page, account settings. Reopening the modal and
saving new choices fires `cc:onChange`, which is logged as a `change` action when the [audit
trail](#consent-audit-trail) is enabled.

Any element works, not just the directive's button — the attribute is what matters:

```blade
<a href="#" data-cc="show-preferencesModal">Cookie settings</a>
```

> **Dynamically inserted triggers:** the library binds these listeners once, when `CookieConsent.run()` executes.
> A trigger injected later (Livewire re-render, Turbo/htmx swap, modal opened from JS) won't be wired up. Call
> `window.CookieConsent.showPreferences()` from your own handler in that case.

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

> **Not an authorization boundary.** `@consent`, `Consent::has()`, and the `consent:*` middleware all read the
> unencrypted `cc_cookie` (it's excluded from `EncryptCookies` because orestbida/cookieconsent needs to read/write
> it as plaintext client-side). A visitor can freely edit that cookie's contents. Treat these checks as a UX/
> compliance gate that reflects the visitor's own stated preference — never as access control for sensitive
> resources; use real authorization (policies, gates, middleware backed by server-side state) for that.

> **`useLocalStorage` warning.** If you set `cookie.useLocalStorage` to `true` in the config, orestbida/cookieconsent
> stores consent in the browser's `localStorage` instead of a cookie. That value never reaches the server, so
> `@consent`/`@endconsent`, `Consent::has()`, `CookieConsent::has()`, and the `consent:*` middleware will **always**
> report "no consent" — they degrade silently to `false`/`[]`/`null`, except for a **one-time** `Log::warning()`
> the first time a read is attempted (guarded by a static flag, so it fires once per PHP process — once per
> request under classic PHP-FPM, once total under Octane). Leave `useLocalStorage` off (the default) if you rely
> on any server-side consent check.

## Consent audit trail

Every accept/change interaction the banner fires (`cc:onFirstConsent`, `cc:onChange`) is POSTed to an internal
`cookieconsent.log` route and written as a new row in `consent_logs`. The `ConsentLog` model's write path is
append-only: it throws if you try to update an existing row through the model. This guard is model-instance-level
only — it does **not** stop raw `DB::table('consent_logs')->update(...)`, a mass `ConsentLog::where(...)->update(...)`,
or `delete()`, all of which bypass Eloquent's `saving` hook. For a hard guarantee, restrict UPDATE/DELETE grants on
the `consent_logs` table at the database user level, or add a database trigger that rejects them outright.

#### Hardening the audit trail at the database level

The model guard only covers writes made through Eloquent instances. For a guarantee that holds even against raw
SQL, `DB::table(...)`, or a compromised app process, revoke `UPDATE`/`DELETE` on `consent_logs` from the
application's database user and grant it only `SELECT`/`INSERT`:

```sql
-- MySQL
REVOKE UPDATE, DELETE ON consent_logs FROM 'app_user'@'%';

-- PostgreSQL
REVOKE UPDATE, DELETE ON consent_logs FROM app_user;
```

The `cookieconsent:prune` command deletes expired rows, so if you apply this hardening, run pruning under a
separate, more privileged database role/connection instead of the app's normal user.

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

> **Delivery.** The consent-log POST does not block or affect the banner UI. It retries transient failures
> (network errors, `429`, `5xx`) up to twice with backoff, and each event carries a server-checked idempotency key
> so a retried or duplicated delivery never creates a second row. If the tab closes or navigates away before the
> request completes, the pending payload is sent as a last-chance `navigator.sendBeacon()` call on `pagehide`/tab-
> hidden. Non-retryable client errors (other `4xx`) and exhausted retries are logged to the browser console, not
> retried further.

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
php artisan cookieconsent:export --format=json --user=42 --user-type="App\\Models\\Admin"
php artisan cookieconsent:export --consent-id=abc123 --from=2026-01-01 --to=2026-06-30
```

`--to` accepts a date-only value (as above) and treats it as inclusive of the whole day — a row created at
`2026-06-30 23:00` is included, not excluded by an implicit midnight bound. `--user` alone matches `user_id` only;
it is **not** scoped by `user_type`, so ids can collide across different morph types (e.g. an `Admin` and a
`Customer` both with id `42`). Pass `--user-type` alongside `--user` to scope the match to a specific morph class.
`--format` only accepts `json` or `csv`; anything else fails with an error and a non-zero exit code.

The underlying `ConsentLog` model exposes matching Eloquent scopes:

```php
ConsentLog::forConsentId('abc123')->get();
ConsentLog::forUser($user)->get();
ConsentLog::between('2026-01-01', '2026-06-30 23:59:59')->get();
// or, with Carbon:
ConsentLog::between('2026-01-01', now()->parse('2026-06-30')->endOfDay())->get();
ConsentLog::latestPerConsentId()->get(); // one row per consent_id, the most recent
```

> **`between()` bounds are used verbatim.** Unlike the export command's `--to`, the `between()` scope passes its
> bounds straight to `whereBetween('created_at', [$from, $to])` — it does **not** auto-expand a date-only end bound
> to end-of-day. `ConsentLog::between('2026-01-01', '2026-06-30')` excludes anything after `2026-06-30 00:00:00`.
> Pass an explicit time (`'2026-06-30 23:59:59'`) or a Carbon `endOfDay()` instance to include the whole day.

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

The resource has no create/edit pages — consent logs are evidence, not editable records. Each row opens a
read-only modal showing every stored field, including `rejected_categories`, `accepted_services`, `policy_hash`
and the raw `payload`.

That modal is a `ViewAction`, which is authorized by a `view()` policy method. If you run Filament's
`strictAuthorization()` and have a `ConsentLog` policy, it now needs `view()` alongside `viewAny()`, otherwise
Filament throws a `LogicException`. Apps with no policy, or without strict authorization, are unaffected.

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

Per-service copy (`loadBtn`, `notice`, ...) comes from `lang/vendor/cookieconsent/{locale}/iframemanager.php`, keyed by
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
