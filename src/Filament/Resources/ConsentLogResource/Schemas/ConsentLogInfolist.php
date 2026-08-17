<?php

namespace Core45\CookieConsent\Filament\Resources\ConsentLogResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConsentLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Consent')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('consent_id')->copyable(),
                    TextEntry::make('action')->badge(),
                    TextEntry::make('accept_type')->badge(),
                    TextEntry::make('revision'),
                    TextEntry::make('user.name')->label('User')->placeholder('Guest'),
                ]),

            Section::make('Categories')
                ->columns(2)
                ->schema([
                    TextEntry::make('accepted_categories')->badge(),
                    TextEntry::make('rejected_categories')->badge()->color('danger'),
                    TextEntry::make('accepted_services')
                        ->formatStateUsing(fn ($state): string => static::formatAcceptedServices($state))
                        ->columnSpanFull(),
                ]),

            Section::make('Policy')
                ->columns(2)
                ->schema([
                    TextEntry::make('policy_version'),
                    TextEntry::make('language_code'),
                    TextEntry::make('policy_hash')->copyable()->columnSpanFull(),
                ]),

            Section::make('Technical')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('ip_address')
                        ->copyable()
                        ->helperText(fn (): ?string => config('cookieconsent.logging.capture_ip') === 'hashed'
                            ? 'Stored as a salted hash, not a readable address.'
                            : null),
                    TextEntry::make('idempotency_key')->copyable(),
                    TextEntry::make('user_agent')->columnSpanFull(),
                    TextEntry::make('payload')
                        ->formatStateUsing(fn ($state): string => static::formatPayload($state))
                        ->copyableState(fn ($state): string => static::formatPayload($state))
                        ->copyable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Render a `category => services` map readably. An empty inner array is the
     * normal case for a category that declares no named services.
     */
    public static function formatAcceptedServices(mixed $state): string
    {
        $state = is_array($state) ? $state : [];

        if ($state === []) {
            return '—';
        }

        $parts = [];

        foreach ($state as $category => $services) {
            $services = array_filter(array_map('strval', (array) $services), fn (string $service): bool => $service !== '');
            $parts[] = $category.': '.($services === [] ? 'None' : implode(', ', $services));
        }

        return implode(' · ', $parts);
    }

    public static function formatPayload(mixed $state): string
    {
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = json_last_error() === JSON_ERROR_NONE ? $decoded : $state;
        }

        return (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
