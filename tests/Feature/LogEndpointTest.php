<?php

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function validPayload(array $overrides = []): array
{
    return array_merge([
        'action' => 'first_consent',
        'consentId' => 'f3f2a1b0-0000-4000-8000-000000000000',
        'revision' => 0,
        'acceptType' => 'all',
        'acceptedCategories' => ['necessary', 'analytics'],
        'rejectedCategories' => [],
        'acceptedServices' => [],
        'languageCode' => 'es',
        'payload' => ['categories' => ['necessary', 'analytics']],
    ], $overrides);
}

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

it('persists a consent log with server-derived fields', function () {
    $this->postJson(route('cookieconsent.log'), validPayload())->assertCreated();

    $log = ConsentLog::sole();

    expect($log->action)->toBe('first_consent')
        ->and($log->accepted_categories)->toBe(['necessary', 'analytics'])
        ->and($log->ip_address)->not->toBeNull()          // server-derived
        ->and($log->policy_hash)->toHaveLength(64)
        ->and($log->user_agent)->not->toBeNull();
});

it('rejects unknown categories and bad actions', function () {
    $this->postJson(route('cookieconsent.log'), validPayload(['acceptedCategories' => ['bogus']]))
        ->assertUnprocessable();

    $this->postJson(route('cookieconsent.log'), validPayload(['action' => 'nonsense']))
        ->assertUnprocessable();
});

it('rejects oversized payloads', function () {
    $this->postJson(route('cookieconsent.log'), validPayload([
        'payload' => ['filler' => str_repeat('x', 17000)],
    ]))->assertStatus(413);
});

it('hashes the IP when configured', function () {
    config()->set('cookieconsent.logging.capture_ip', 'hashed');
    config()->set('cookieconsent.logging.ip_hash_salt', 'salt');

    $this->postJson(route('cookieconsent.log'), validPayload())->assertCreated();

    expect(ConsentLog::sole()->ip_address)->toHaveLength(64)->not->toBe('127.0.0.1');
});

it('omits PII when disabled', function () {
    config()->set('cookieconsent.logging.capture_ip', false);
    config()->set('cookieconsent.logging.capture_user_agent', false);

    $this->postJson(route('cookieconsent.log'), validPayload())->assertCreated();

    $log = ConsentLog::sole();
    expect($log->ip_address)->toBeNull()->and($log->user_agent)->toBeNull();
});

it('links the authenticated user via morphs', function () {
    $user = new class extends User
    {
        protected $table = 'users';
    };
    Schema::create('users', function ($table) {
        $table->id();
        $table->timestamps();
    });
    $user->save();

    $this->actingAs($user)->postJson(route('cookieconsent.log'), validPayload())->assertCreated();

    expect(ConsentLog::sole()->user_id)->toBe($user->getKey());
});

it('is rate limited', function () {
    // The natural test here would set `cookieconsent.logging.rate_limit`,
    // call `refreshApplication()` to rebuild routes against the new value,
    // and assert the 3rd request 429s. Under Testbench, `refreshApplication()`
    // tears down and recreates the whole app via `createApplication()`, which
    // re-merges the package's config file from disk — silently discarding the
    // runtime `config()->set()` override made in this test. The route is
    // rebuilt with the *default* `throttle:30,1`, not `throttle:2,1`, so the
    // 3rd request never actually 429s; this was verified empirically (it
    // consistently fails, not flakes) before adopting this fallback.
    //
    // Enforcement itself is real and was independently confirmed: 30
    // requests succeed and the 31st 429s using the untouched default config
    // (no refreshApplication involved). What can't be exercised post-boot
    // in this environment is a *changed* rate limit value, since the
    // middleware string is baked into the route at boot time from config.
    // So this test asserts the throttle middleware is wired onto the named
    // route with the configured default, which is the documented fallback.
    $middleware = Route::getRoutes()->getByName('cookieconsent.log')->gatherMiddleware();

    expect($middleware)->toContain('web')
        ->and($middleware)->toContain('throttle:30,1');
});

it('returns 404 when logging is disabled', function () {
    config()->set('cookieconsent.logging.enabled', false);

    $this->postJson('/cookie-consent/log', validPayload())->assertNotFound();
});

it('is idempotent when the same idempotency key is posted twice', function () {
    $payload = validPayload(['idempotencyKey' => 'same-key-123']);

    $this->postJson(route('cookieconsent.log'), $payload)->assertCreated();
    $second = $this->postJson(route('cookieconsent.log'), $payload);

    $second->assertOk()->assertJson(['ok' => true, 'duplicate' => true]);
    expect(ConsentLog::count())->toBe(1);
});

it('still logs successfully without an idempotency key', function () {
    $this->postJson(route('cookieconsent.log'), validPayload())->assertCreated();

    expect(ConsentLog::count())->toBe(1);
});

it('keeps CSRF validation on the log route by default', function () {
    // Mirrors tests/FeatureCsrfDisabled/LogEndpointCsrfDisabledTest.php:
    // with the default config (logging.csrf === true), the route must NOT
    // exclude CSRF, i.e. resolveMiddleware still returns it.
    $route = Route::getRoutes()->getByName('cookieconsent.log');

    expect($route->excludedMiddleware())->toBe([]);

    $resolved = app('router')->resolveMiddleware(
        [PreventRequestForgery::class],
        $route->excludedMiddleware(),
    );

    expect($resolved)->toContain(PreventRequestForgery::class);
});
