<?php

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedLog(array $overrides = []): ConsentLog
{
    return ConsentLog::create(array_merge([
        'consent_id' => 'abc-123',
        'action' => 'first_consent',
        'accept_type' => 'all',
        'accepted_categories' => ['necessary'],
        'rejected_categories' => [],
        'revision' => 0,
        'payload' => [],
    ], $overrides));
}

it('exports json lines filtered by consent id', function () {
    seedLog();
    seedLog(['consent_id' => 'other']);

    $this->artisan('cookieconsent:export', ['--consent-id' => 'abc-123'])
        ->expectsOutputToContain('"consent_id":"abc-123"')
        ->doesntExpectOutputToContain('"consent_id":"other"')
        ->assertSuccessful();
});

it('exports csv with a header row', function () {
    seedLog();

    $this->artisan('cookieconsent:export', ['--format' => 'csv'])
        ->expectsOutputToContain('consent_id')
        ->assertSuccessful();
});

it('prunes only when retention is configured', function () {
    seedLog(['created_at' => now()->subDays(400)]);
    seedLog();

    $this->artisan('cookieconsent:prune')->assertSuccessful();
    expect(ConsentLog::count())->toBe(2);

    config()->set('cookieconsent.logging.retention_days', 365);

    $this->artisan('cookieconsent:prune')->assertSuccessful();
    expect(ConsentLog::count())->toBe(1);
});
