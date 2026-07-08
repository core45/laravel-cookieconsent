<?php

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeLog(array $overrides = []): ConsentLog
{
    return ConsentLog::create(array_merge([
        'consent_id' => 'abc-123',
        'action' => 'first_consent',
        'accept_type' => 'all',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => [],
        'revision' => 0,
        'payload' => ['categories' => ['necessary', 'analytics']],
    ], $overrides));
}

it('creates an append-only row with json casts', function () {
    $log = makeLog();

    expect($log->exists)->toBeTrue()
        ->and($log->accepted_categories)->toBe(['necessary', 'analytics'])
        ->and($log->updated_at)->toBeNull();
});

it('rejects updates to existing rows', function () {
    $log = makeLog();

    expect(fn () => $log->update(['action' => 'change']))->toThrow(RuntimeException::class);
});

it('filters via scopes', function () {
    makeLog();
    makeLog(['consent_id' => 'other-id', 'action' => 'change']);

    expect(ConsentLog::forConsentId('abc-123')->count())->toBe(1)
        ->and(ConsentLog::between(now()->subDay(), now()->addDay())->count())->toBe(2)
        ->and(ConsentLog::latestPerConsentId()->count())->toBe(2);
});
