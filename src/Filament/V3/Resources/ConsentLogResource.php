<?php

namespace Core45\CookieConsent\Filament\V3\Resources;

use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource\Infolists\ConsentLogInfolist;
use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource\Pages\ListConsentLogs;
use Core45\CookieConsent\Models\ConsentLog;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Filament v3 build of the read-only consent-log resource.
 *
 * Kept feature-identical to the v4/v5 resource in ../../Resources. Only the API
 * differs: v3 takes an Infolist rather than a Schema, and row actions are
 * registered with actions() rather than recordActions(). Never referenced from
 * the v4/v5 tree, and never loaded unless FilamentVersion picks it.
 */
class ConsentLogResource extends Resource
{
    protected static ?string $model = ConsentLog::class;

    public static function infolist(Infolist $infolist): Infolist
    {
        return ConsentLogInfolist::configure($infolist);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('consent_id')
                    ->searchable()
                    ->limit(20)
                    ->copyable()
                    // Without this, copying yields the ...-truncated display value.
                    ->copyableState(fn ($state): string => (string) $state),
                TextColumn::make('action')->badge(),
                TextColumn::make('accept_type')->badge(),
                TextColumn::make('accepted_categories')
                    ->formatStateUsing(fn ($state): string => implode(', ', (array) $state)),
                TextColumn::make('policy_version')->toggleable(),
                TextColumn::make('language_code')->toggleable(),
                TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')->options([
                    'first_consent' => 'First consent',
                    'change' => 'Change',
                ]),
                SelectFilter::make('accept_type')->options([
                    'all' => 'All',
                    'custom' => 'Custom',
                    'necessary' => 'Necessary',
                ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsentLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
