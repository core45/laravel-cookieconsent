<?php

namespace Core45\CookieConsent\Filament\Resources;

use Core45\CookieConsent\Filament\Resources\ConsentLogResource\Pages\ListConsentLogs;
use Core45\CookieConsent\Models\ConsentLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConsentLogResource extends Resource
{
    protected static ?string $model = ConsentLog::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('consent_id')->searchable()->limit(20),
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
