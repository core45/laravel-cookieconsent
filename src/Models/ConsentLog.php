<?php

namespace Core45\CookieConsent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

class ConsentLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'accepted_categories' => 'array',
        'rejected_categories' => 'array',
        'accepted_services' => 'array',
        'payload' => 'array',
        'revision' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $log): void {
            if ($log->exists) {
                throw new RuntimeException('Consent logs are append-only and cannot be updated.');
            }
        });
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForConsentId(Builder $query, string $consentId): Builder
    {
        return $query->where('consent_id', $consentId);
    }

    public function scopeForUser(Builder $query, Model $user): Builder
    {
        return $query->where('user_type', $user::class)->where('user_id', $user->getKey());
    }

    public function scopeBetween(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeLatestPerConsentId(Builder $query): Builder
    {
        return $query->whereIn('id', static::query()->selectRaw('max(id)')->groupBy('consent_id'));
    }
}
