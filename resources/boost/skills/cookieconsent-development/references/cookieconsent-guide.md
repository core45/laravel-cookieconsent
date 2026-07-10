# core45/laravel-cookieconsent Guide

Laravel wrapper for orestbida/cookieconsent v3 and iframemanager. Bundles both self-hosted (no CDN request); assets are published to `public/vendor/cookieconsent`.

## Installation

```bash
composer require core45/laravel-cookieconsent
php artisan vendor:publish --tag=cookieconsent-config
php artisan vendor:publish --tag=cookieconsent-assets
php artisan migrate
```

Optional tags: `cookieconsent-lang` (to `lang/vendor/cookieconsent/{locale}/*.php`), `cookieconsent-views`, `cookieconsent-migrations`.

Add `@cookieconsent` to the layout `<head>` — recommended placement; the banner script defers to `DOMContentLoaded`. The package auto-registers `cc_cookie` as an `EncryptCookies` exception on boot.

## Configuration (`config/cookieconsent.php`)

Every key **except** `translations_mode`, `csp_nonce`, `logging`, and `iframemanager` is passed straight to `CookieConsent.run()` (see orestbida's configuration reference for all options).

```php
return [
    'cookie' => ['name' => 'cc_cookie', 'expiresAfterDays' => 182],
    'mode' => 'opt-in',
    'revision' => 0,
    'guiOptions' => [
        'consentModal' => ['layout' => 'box', 'position' => 'bottom left'],
        'preferencesModal' => ['layout' => 'box'],
    ],
    'categories' => [
        'necessary' => ['enabled' => true, 'readOnly' => true],
        'analytics' => [
            'autoClear' => ['cookies' => [['name' => '_gid']]],
        ],
    ],
    // Package-only keys:
    'translations_mode' => 'active', // 'active' = current locale only, 'all' = every published locale
    'csp_nonce' => null,
    'logging' => [/* see Audit trail */],
    'iframemanager' => [/* see iframemanager */],
];
```

**RegExp cookie matching:** `autoClear.cookies[].name` is an exact string match by default. For a JS `RegExp`, use the helper — it emits a sentinel the Blade view revives into `new RegExp(...)`:

```php
use Core45\CookieConsent\Facades\CookieConsent;

['name' => CookieConsent::regex('^_ga')],        // matches _ga, _ga_ABC123, ...
['name' => CookieConsent::regex('^_gid$', 'i')], // second arg = JS RegExp flags
```

**`useLocalStorage` warning:** setting `cookie.useLocalStorage => true` stores consent in localStorage, which never reaches the server. All server-side reads (`@consent`, `Consent::has()`, `CookieConsent::has()`, `consent:*` middleware) then silently return `false`/`[]`/`null` (with a one-time `Log::warning()` per PHP process). Leave it off if any server-side check is used.

## Translations

Standard Laravel lang files at `lang/vendor/cookieconsent/{locale}/cookieconsent.php` (maps onto orestbida's `language.translations.{locale}` shape: `consentModal`, `preferencesModal`, ...) and `iframemanager.php` (per-service `loadBtn`/`notice`/... strings). Ships `en` and `es`.

`translations_mode`: `'active'` (default) embeds only `app()->getLocale()`; `'all'` embeds every published locale so `CookieConsent.setLanguage()` works client-side without a reload.

## Script gating (`@cookiescript`)

Emits blocked `<script type="text/plain" data-category="...">` tags that orestbida unblocks on consent.

```blade
{{-- External --}}
@cookiescript('analytics', 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX')

{{-- Inline --}}
@cookiescript('analytics')
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-XXXXXXX');
@endcookiescript

{{-- Negated: runs when consent is absent or withdrawn --}}
@cookiescript('!analytics')
    // cleanup code
@endcookiescript

{{-- Extra HTML attributes as third argument --}}
@cookiescript('analytics', 'https://example.com/ga.js', ['data-service' => 'Google Analytics'])
```

Blade quirk: `@endcookiescript` must not be glued directly onto a word character (`someCode@endcookiescript` won't compile as a directive boundary).

## Server-side consent

```blade
@consent('analytics')
    <p>Analytics-only content.</p>
@endconsent
```

```php
use Core45\CookieConsent\Facades\CookieConsent;
use Core45\CookieConsent\Support\Consent;

app(Consent::class)->has('analytics');   // bool
CookieConsent::has('analytics');         // same via facade
CookieConsent::categories();             // list<string>
CookieConsent::acceptType();             // 'all' | 'necessary' | 'custom' | null (no cookie yet)

// Route gating — aborts 403 when consent is missing:
Route::get('/dashboard/analytics', AnalyticsController::class)
    ->middleware('consent:analytics');
```

**Not an authorization boundary.** These read the unencrypted, visitor-editable `cc_cookie`. Treat them as UX/compliance gates only; use real authorization for sensitive resources.

## Consent audit trail

Banner events (`cc:onFirstConsent`, `cc:onChange`) POST to an internal `cookieconsent.log` route and insert rows into `consent_logs`. The `ConsentLog` model write path is append-only (throws on update through a model instance) — but raw `DB::table()->update()`, mass `ConsentLog::where()->update()`, and `delete()` bypass it. For a hard guarantee, revoke `UPDATE`/`DELETE` on `consent_logs` from the app DB user (then run pruning under a privileged connection).

Columns include: `consent_id`, `revision`, `action` (`first_consent`|`change`), `accept_type`, `accepted_categories`/`rejected_categories`/`accepted_services` (JSON), `language_code`, `policy_version`, `policy_hash` (see `CookieConsent::policyHash()`), `ip_address`, `user_agent`, `user_type`/`user_id` (polymorphic), `payload`. Evidentiary fields are derived server-side, never trusted from client input.

Delivery is non-blocking, retries transient failures twice with backoff, is idempotent (server-checked key — no duplicate rows), and falls back to `navigator.sendBeacon()` on `pagehide`.

### Logging config

```php
'logging' => [
    'enabled' => true,
    'csrf' => true,
    'capture_ip' => 'raw',            // 'raw' | 'hashed' | false
    'ip_hash_salt' => env('COOKIECONSENT_IP_SALT'),
    'capture_user_agent' => true,
    'link_user' => true,
    'morph_id_type' => 'int',         // 'int' | 'uuid' | 'ulid' | 'string' — match the User key type
    'policy_version' => null,
    'retention_days' => null,         // null disables pruning
    'rate_limit' => '30,1',
],
```

Storing IP/user agent is itself processing of personal data — tune `capture_ip` and `retention_days` for data minimization. `$request->ip()` accuracy depends on the app's `trustProxies` configuration.

### Retrieval and retention

```bash
php artisan cookieconsent:export --format=csv > consent-evidence.csv
php artisan cookieconsent:export --format=json --user=42 --user-type="App\\Models\\Admin"
php artisan cookieconsent:export --consent-id=abc123 --from=2026-01-01 --to=2026-06-30
php artisan cookieconsent:prune   # deletes rows older than retention_days; no-op when null
```

Export `--to` with a date-only value is inclusive of the whole day. `--user` alone matches `user_id` across all morph types — add `--user-type` to scope. `--format` accepts only `json` or `csv`.

Eloquent scopes:

```php
ConsentLog::forConsentId('abc123')->get();
ConsentLog::forUser($user)->get();
ConsentLog::between('2026-01-01', now()->parse('2026-06-30')->endOfDay())->get();
ConsentLog::latestPerConsentId()->get(); // most recent row per consent_id
```

**`between()` uses bounds verbatim** — unlike export's `--to`, a date-only end bound means midnight, excluding the rest of that day. Pass an explicit time or Carbon `endOfDay()`.

Schedule pruning: `Schedule::command('cookieconsent:prune')->daily();` in `routes/console.php`.

### Filament

Optional read-only resource (requires `filament/filament` ^5.0), no create/edit pages:

```php
$panel->plugin(\Core45\CookieConsent\Filament\CookieConsentFilamentPlugin::make());
```

## iframemanager (click-to-load embeds)

```php
'iframemanager' => [
    'enabled' => true,
    'services' => [
        'youtube' => [
            'category' => 'analytics', // cookieconsent category that unlocks this service
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/{data-id}',
            'thumbnailUrl' => 'https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg',
            'iframe' => ['allow' => 'accelerometer; autoplay; encrypted-media'],
        ],
    ],
],
```

```blade
@cookieconsent
@iframemanager   {{-- place right after @cookieconsent --}}
...
@iframe('youtube', $videoId, ['params' => 'rel=0', 'thumbnail' => $customThumbnailUrl])
```

Per-service copy lives in `lang/vendor/cookieconsent/{locale}/iframemanager.php`, keyed by service name.

## Advanced JS

**`cc:*` window events:** `cc:onFirstConsent`, `cc:onConsent`, `cc:onChange`, `cc:onModalReady`, `cc:onModalShow`, `cc:onModalHide`.

```html
<script>
window.addEventListener('cc:onConsent', () => {
    if (window.CookieConsent.acceptedCategory('analytics')) { /* ... */ }
});
</script>
```

**`cookieconsent:config` push stack** — runs after the `config` JS variable is declared but before `CookieConsent.run(config)`, so `config` can be mutated:

```blade
@push('cookieconsent:config')
<script>
    config.cookie.expiresAfterDays = () => 365;
</script>
@endpush
```

**Google Consent Mode v2 recipe:** set `gtag('consent', 'default', {...denied})` in the push stack, then `gtag('consent', 'update', {...granted})` inside a `cc:onConsent` listener guarded by `acceptedCategory()`.

## Content Security Policy

```php
'csp_nonce' => fn () => \Illuminate\Support\Facades\Vite::cspNonce(),
```

Package-rendered script tags (`@cookieconsent`, `@iframemanager`) get `nonce="..."` automatically. Scripts pushed via `@push('cookieconsent:config')` do **not** — add the nonce to those manually.
