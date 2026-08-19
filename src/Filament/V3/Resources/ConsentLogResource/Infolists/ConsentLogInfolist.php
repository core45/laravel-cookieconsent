<?php

namespace Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource\Infolists;

use Core45\CookieConsent\Filament\ConsentLogFormatter;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

/**
 * Filament v3 build of the consent-log detail view. Field-for-field identical
 * to the v4/v5 version; v3 has no schemas package, so the layout components
 * come from Filament\Infolists and the entries go through schema() rather than
 * components().
 */
class ConsentLogInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist->schema([
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

    public static function formatAcceptedServices(mixed $state): string
    {
        return ConsentLogFormatter::acceptedServices($state);
    }

    public static function formatPayload(mixed $state): string
    {
        return ConsentLogFormatter::payload($state);
    }
}
