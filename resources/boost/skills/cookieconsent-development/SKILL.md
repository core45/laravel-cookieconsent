---
name: cookieconsent-development
description: Build and work with core45/laravel-cookieconsent features including cookie banner setup, consent categories, GDPR script gating with Blade directives, server-side consent checks, click-to-load iframe embeds, and the consent_logs audit trail.
license: MIT
metadata:
  author: core45
---

# Cookie Consent Development

## Overview
Use core45/laravel-cookieconsent to add a GDPR-compliant cookie banner (orestbida/cookieconsent v3, self-hosted — no CDN) to a Laravel app. It provides PHP-first config, Laravel lang-file translations, Blade-driven script gating, server-side consent reads, iframemanager click-to-load embeds, and an append-only `consent_logs` audit trail.

## When to Activate
- Activate when adding or configuring a cookie banner, consent modal, or GDPR/ePrivacy consent flow.
- Activate when code references `@cookieconsent`, `@cookiescript`, `@consent`, `@cookiepreferences`, `@iframemanager`, `@iframe`, the `CookieConsent` facade, `consent:*` middleware, or the `ConsentLog` model.
- Activate when gating analytics/marketing scripts or third-party embeds (YouTube, maps) behind consent.

## Scope
- In scope: banner config, consent categories, script gating, server-side consent checks, consent logging/export/pruning, iframemanager embeds, CSP nonces, translations.
- Out of scope: writing a consent solution from scratch, other consent packages, non-Laravel frameworks.

## Workflow
1. Identify the task (setup, script gating, server-side reads, audit trail, embeds).
2. Read `references/cookieconsent-guide.md` and focus on the relevant section.
3. Apply the patterns from the reference, keeping code minimal and Laravel-native.

## Core Concepts

### Setup
```bash
composer require core45/laravel-cookieconsent
php artisan vendor:publish --tag=cookieconsent-config
php artisan vendor:publish --tag=cookieconsent-assets
php artisan migrate
```
Add `@cookieconsent` to the layout's `<head>` (recommended placement; the script waits for `DOMContentLoaded`). Add `@iframemanager` right after it if using embeds. The package auto-excludes its `cc_cookie` from `EncryptCookies` — no `bootstrap/app.php` edits needed.

### Configuration
`config/cookieconsent.php` is passed almost verbatim to `CookieConsent.run()`. Package-only keys stripped before reaching JS: `translations_mode`, `csp_nonce`, `logging`, `iframemanager`. Define categories under `categories` (e.g. `necessary` with `readOnly => true`, plus `analytics`, `marketing`, ...). For RegExp cookie autoClear matching use `CookieConsent::regex('^_ga')` — plain strings are exact matches.

### Script Gating
```blade
@cookiescript('analytics', 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX')

@cookiescript('analytics')
    gtag('config', 'G-XXXXXXX');
@endcookiescript

@cookiescript('!analytics') {{-- runs when consent is absent/withdrawn --}}
    // cleanup
@endcookiescript
```

### Reopening the Preferences Modal
The banner never reappears on its own after it is answered, so every site needs a withdrawal trigger (GDPR: withdrawing must be as easy as giving).
```blade
@cookiepreferences
@cookiepreferences('Cookie settings', ['class' => 'footer__link'])
```
Renders `<button type="button" data-cc="show-preferencesModal">…</button>`. Default label is the `consentModal.showPreferencesBtn` translation line (fallback `Manage preferences`). `data-cc`, `type`, and `on*` handlers are dropped from the attribute array. Any element with `data-cc="show-preferencesModal"` works too. Listeners bind once at `CookieConsent.run()` — for triggers injected later (Livewire, Turbo), call `window.CookieConsent.showPreferences()` instead.

### Server-Side Consent Reads
```blade
@consent('analytics')
    <p>Analytics-only content.</p>
@endconsent
```
```php
use Core45\CookieConsent\Facades\CookieConsent;

CookieConsent::has('analytics');   // bool
CookieConsent::categories();       // ['necessary', 'analytics']
CookieConsent::acceptType();       // 'all' | 'necessary' | 'custom' | null

Route::get('/x', Controller::class)->middleware('consent:analytics'); // 403 without consent
```
These read the visitor-editable `cc_cookie` — treat as UX/compliance gating, never authorization.

### Click-to-Load Embeds
Configure services under `config('cookieconsent.iframemanager.services')`, then:
```blade
@iframe('youtube', $videoId, ['params' => 'rel=0'])
```

### Audit Trail
Consent decisions are logged to `consent_logs` (append-only model write path). Retrieve with `php artisan cookieconsent:export --format=csv`, prune with `php artisan cookieconsent:prune` (needs `logging.retention_days`), or use `ConsentLog` scopes: `forConsentId()`, `forUser()`, `between()`, `latestPerConsentId()`. Optional read-only Filament resource: `$panel->plugin(CookieConsentFilamentPlugin::make())`.

## Do and Don't

- **Do** keep `@cookieconsent` in `<head>` and gate every non-essential script with `@cookiescript`.
- **Do** set `logging.capture_ip => 'hashed'` or `false` and `retention_days` to match data-minimization obligations.
- **Do** use `translations_mode => 'all'` when the app switches locales client-side without a reload.
- **Don't** enable `cookie.useLocalStorage` if any server-side consent check is used — `@consent`, `CookieConsent::has()`, and `consent:*` middleware silently report "no consent".
- **Don't** use `@consent` or `consent:*` middleware as an authorization boundary — the cookie is unencrypted and visitor-editable.
- **Don't** assume `ConsentLog::between('2026-01-01', '2026-06-30')` includes June 30 — the scope uses bounds verbatim; pass `endOfDay()` or an explicit time (unlike the export command's inclusive `--to`).
- **Don't** forget to add your own `nonce="..."` to scripts pushed via `@push('cookieconsent:config')` under a strict CSP — only package-rendered tags get the configured `csp_nonce` automatically.
