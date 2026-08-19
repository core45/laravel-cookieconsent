# Changelog

All notable changes to `core45/laravel-cookieconsent` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Nothing yet.

## [3.2.0] - 2026-08-19

Filament v3 and v4 are now supported alongside v5.

### Added

- **Filament v3 and v4 support.** The optional consent-log admin resource previously worked only with Filament v5,
  but nothing enforced that — the package requires no `filament/*` at all, and both Filament 3.3.54 and 4.12.6
  accept `illuminate ^13.0`. Installing on Laravel 13 with Filament v3 therefore succeeded silently and then died
  at class-load time with `class Filament\Schemas\Schema is not available`. Registration is unchanged for callers:
  `$panel->plugin(CookieConsentFilamentPlugin::make())` now detects the installed major and registers the matching
  resource.
- `Core45\CookieConsent\Filament\FilamentVersion` — detection helper exposing `isInstalled()`, `usesSchemas()`,
  `major()`, `version()` and `resourceClass()`. Use `resourceClass()` if you reference the resource directly rather
  than through the plugin.
- `Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource` and its page and infolist — the Filament v3
  build, feature-identical to the v4/v5 one.
- `Core45\CookieConsent\Filament\ConsentLogFormatter` — the presentation helpers both builds share. Imports nothing
  from Filament, so it loads under any major.
- `php artisan cookieconsent:filament` — reports the detected Filament version, the infolist API in use, and the
  resource class that will be registered.
- CI rows for Filament v4, v3 and none, on top of the existing v5 rows.
- `.playwright-mcp/` is gitignored.

### Fixed

- The Filament test guard was version-blind. It skipped on `! class_exists(Filament\Panel::class)`, which is true
  on v3 as well, so the v3 incompatibility surfaced as a fatal error rather than a skip. Each suite now keys off
  the schemas probe instead.
- The Filament CI axis had to become part of the base matrix rather than `include`-only. A GitHub Actions `include`
  entry naming only keys that already match a base combination is *merged into* that combination instead of
  appending a row, which would have collapsed all three Filament rows onto one and tested no alternate major.
- The console-output assertion now uses one `expectsOutputToContain` per invocation. `laravel/framework` 13.26
  matches each chained expectation against a single successive line of output, where 13.25 searched the whole
  buffer, so a chain passed locally and failed on CI.

### Notes

Verified by running the full suite against five installs: no Filament, Filament 3.3.54, 4.12.6 and 5.7.6 on
Laravel 13, plus the stepped-back Laravel 12 toolchain. In each, the version-matched tests ran and the others
skipped. No breaking changes.

## [3.1.0] - 2026-08-17

### Added

- A read-only detail view on the consent-log resource. Each row opens a modal showing every stored field,
  including `rejected_categories`, `accepted_services`, `policy_hash` and the raw `payload`.
- 12 further locales, bringing the shipped set to 22.

### Fixed

- Wording defects in the newly added locales.

### Notes

The detail modal is a `ViewAction`, authorized by a `view()` policy method. If you run Filament's
`strictAuthorization()` and have a `ConsentLog` policy, it now needs `view()` alongside `viewAny()`. Apps with no
policy, or without strict authorization, are unaffected.

## [3.0.0] - 2026-08-17

### Removed

- **BREAKING: Laravel 11 support.** Every `laravel/framework` 11.x release is covered by a security advisory with
  no fixed 11.x version (`PKSA-mdq4-51ck-6kdq` affects `>=11.0.0,<12.0.0`), so Composer refuses to install Laravel
  11 under default advisory settings and it will not be patched. Advertising support for it was misleading — the
  constraint could not resolve. `illuminate/support`, `illuminate/http` and `illuminate/database` are now
  `^12.0|^13.0`. Projects on Laravel 11 must stay on the 2.x line.

### Changed

- The Laravel 12 CI row steps its test toolchain back to testbench 10 and pest 4, since pest 5 and testbench 11
  both require `laravel/framework ^13.23.0`.
- CI tests PHP 8.5, with an advisory nightly row for 8.6.

### Fixed

- Test assertions no longer pin the localized `loadBtn` string to Laravel 13, and correctly decode
  double-escaped unicode.

## [2.0.0] - 2026-08-17

### Added

- 8 locales — `de`, `hr`, `it`, `pl`, `pt`, `sl`, `sv`, `uk` — alongside the existing `en` and `es`, for both
  `cookieconsent.php` and `iframemanager.php`. All are key-identical to `en` and are picked up automatically by
  `ConfigBuilder::availableLocales()`, so no registration is needed.

### Changed

- **BREAKING: requires PHP 8.4 or newer**, matching the upgraded dev toolchain (Pest 5 and PHPUnit 13 both require
  8.4). Projects on PHP 8.2 or 8.3 must stay on the 1.x line.
- `ConfigBuilderTest` asserts the exact locale set, so adding a locale without updating it fails the build.

### Fixed

- Copy defects in the new locales: `pl` used the ungrammatical "które kategorie pozwalasz" and "Zasadniczo
  niezbędne"; `hr` mixed first-person "Prihvaćam sve" with the imperative "Odbij sve"; `pt` mixed Brazilian "Você
  decide" with European "Gerir"/"Guardar".

## [1.1.0] - 2026-08-11

### Added

- `@cookiepreferences` Blade directive for reopening the consent modal.

## [1.0.2] - 2026-07-10

### Added

- Laravel Boost AI guidelines and the `cookieconsent-development` skill.

## [1.0.1] - 2026-07-09

Re-tag of 1.0.0; no code changes.

## [1.0.0] - 2026-07-09

Initial release.

### Added

- `@cookieconsent` Blade directive with a config reviver, a pre-run stack, CSP nonce support and logging listeners.
- `@consent` directive and a `consent` route middleware for gating server-rendered content.
- `@cookiescript` directives for orestbida script gating.
- `@iframemanager` / `@iframe` directives with localized services and consent glue.
- Server-side consent reader, with an `EncryptCookies` exclusion and graceful degradation when `useLocalStorage`
  is on.
- Consent logging endpoint with validation, PII configuration and rate limiting.
- Append-only `consent_logs` table and `ConsentLog` model.
- `cookieconsent:export` and `cookieconsent:prune` Artisan commands for consent evidence.
- Canonicalized policy hash for consent evidence.
- Lang-file i18n injected into orestbida translations, shipping `en` and `es`.
- Optional Filament v5 plugin with a read-only `ConsentLog` resource.
- Manager, facade and regex sentinel helper.
- Config file and `ConfigBuilder` passthrough.
- Vendored cookieconsent 3.1.0 and iframemanager 1.3.0 assets.
- End-to-end browser coverage for the banner, logging and script gating.

[Unreleased]: https://github.com/core45/laravel-cookieconsent/compare/v3.2.0...HEAD
[3.2.0]: https://github.com/core45/laravel-cookieconsent/compare/v3.1.0...v3.2.0
[3.1.0]: https://github.com/core45/laravel-cookieconsent/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/core45/laravel-cookieconsent/compare/v2.0.0...v3.0.0
[2.0.0]: https://github.com/core45/laravel-cookieconsent/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/core45/laravel-cookieconsent/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/core45/laravel-cookieconsent/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/core45/laravel-cookieconsent/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/core45/laravel-cookieconsent/releases/tag/v1.0.0
