<?php

use Core45\CookieConsent\Filament\CookieConsentFilamentPlugin;
use Core45\CookieConsent\Filament\Resources\ConsentLogResource;
use Core45\CookieConsent\Filament\Resources\ConsentLogResource\Pages\ListConsentLogs;
use Core45\CookieConsent\Filament\Resources\ConsentLogResource\Schemas\ConsentLogInfolist;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * @return array<int, string>
 */
function consentLogInfolistEntryNames(): array
{
    $schema = ConsentLogResource::infolist(Schema::make(new ListConsentLogs));

    return array_values(array_map(
        fn (TextEntry $entry): string => $entry->getName(),
        array_filter($schema->getFlatComponents(), fn ($component): bool => $component instanceof TextEntry),
    ));
}

function consentLogTable(): Table
{
    return ConsentLogResource::table(Table::make(new ListConsentLogs));
}

function consentLogInfolistEntry(string $name): TextEntry
{
    $schema = ConsentLogResource::infolist(Schema::make(new ListConsentLogs));

    foreach ($schema->getFlatComponents() as $component) {
        if ($component instanceof TextEntry && $component->getName() === $name) {
            return $component;
        }
    }

    throw new RuntimeException("No infolist entry named [{$name}].");
}

it('registers the resource on a panel via the plugin', function () {
    $panel = Panel::make()->id('testing');

    CookieConsentFilamentPlugin::make()->register($panel);

    expect($panel->getResources())->toContain(ConsentLogResource::class);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('is a read-only resource', function () {
    expect(ConsentLogResource::canCreate())->toBeFalse();
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('exposes an infolist with entries', function () {
    $schema = ConsentLogResource::infolist(Schema::make(new ListConsentLogs));

    expect($schema)->toBeInstanceOf(Schema::class)
        ->and($schema->getComponents())->not->toBeEmpty()
        ->and(consentLogInfolistEntryNames())->not->toBeEmpty();
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('shows in the infolist the columns the table omits', function () {
    $names = consentLogInfolistEntryNames();

    $required = [
        'policy_hash',
        'rejected_categories',
        'accepted_services',
        'payload',
        'revision',
        'idempotency_key',
        'user_agent',
    ];

    expect(array_values(array_intersect($required, $names)))->toBe($required);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('registers a view action on the table', function () {
    $actions = consentLogTable()->getRecordActions();

    expect($actions)->not->toBeEmpty();

    $classes = array_map(fn ($action): string => $action::class, $actions);

    expect($classes)->toContain(ViewAction::class);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('registers no edit action on the table', function () {
    $classes = array_map(fn ($action): string => $action::class, consentLogTable()->getRecordActions());

    expect($classes)->not->toContain(EditAction::class);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('formats accepted services readably', function () {
    $formatted = ConsentLogInfolist::formatAcceptedServices([
        'necessary' => [],
        'analytics' => ['ga4'],
    ]);

    expect($formatted)->toBe('necessary: None · analytics: ga4')
        ->and($formatted)->not->toContain('Array');
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('wires the accepted services formatter to the infolist entry', function () {
    $rendered = (string) consentLogInfolistEntry('accepted_services')->formatState([
        'necessary' => [],
        'analytics' => ['ga4'],
    ]);

    expect($rendered)->toContain('necessary: None')
        ->and($rendered)->toContain('analytics: ga4')
        ->and($rendered)->not->toContain('Array');
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('wires the payload pretty-printer to the infolist entry', function () {
    $entry = consentLogInfolistEntry('payload');
    $state = ['categories' => ['necessary'], 'url' => 'https://example.com/a'];

    expect((string) $entry->formatState($state))->toContain("\n    ")
        ->and($entry->getCopyableState($state))->toContain("\n    ")
        ->and($entry->getCopyableState($state))->toContain('https://example.com/a');
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('copies the untruncated consent id from the table', function () {
    $column = consentLogTable()->getColumn('consent_id');
    $uuid = '2f1c9f6e-0f2f-4f4a-9c1d-6d0d1f2a3b4c';

    expect($column->formatState($uuid))->not->toBe($uuid)
        ->and($column->getCopyableState($uuid))->toBe($uuid);
})->skip(! class_exists(Panel::class), 'Filament is not installed.');

it('pretty-prints the payload', function () {
    $formatted = ConsentLogInfolist::formatPayload(['categories' => ['necessary'], 'url' => 'https://example.com/a']);

    expect($formatted)->toContain("\n    ")
        ->and($formatted)->toContain('https://example.com/a');
})->skip(! class_exists(Panel::class), 'Filament is not installed.');
