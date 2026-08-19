<?php

use Core45\CookieConsent\Filament\CookieConsentFilamentPlugin;
use Core45\CookieConsent\Filament\FilamentVersion;
use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource;
use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource\Infolists\ConsentLogInfolist;
use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource\Pages\ListConsentLogs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Panel;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

/**
 * Mirror of FilamentPluginTest for the Filament v3 resource. The two files can
 * never run in the same process — v3 and v4+ cannot be installed side by side —
 * so each is guarded on the schemas probe rather than on Filament being present
 * at all. Helper names differ from the v4+ file because Pest loads both.
 *
 * @return array<int, string>
 */
function v3ConsentLogInfolistEntryNames(): array
{
    $infolist = ConsentLogResource::infolist(Infolist::make(new ListConsentLogs));

    return array_values(array_map(
        fn (TextEntry $entry): string => $entry->getName(),
        array_filter($infolist->getFlatComponents(), fn ($component): bool => $component instanceof TextEntry),
    ));
}

function v3ConsentLogTable(): Table
{
    return ConsentLogResource::table(Table::make(new ListConsentLogs));
}

function v3ConsentLogInfolistEntry(string $name): TextEntry
{
    $infolist = ConsentLogResource::infolist(Infolist::make(new ListConsentLogs));

    foreach ($infolist->getFlatComponents() as $component) {
        if ($component instanceof TextEntry && $component->getName() === $name) {
            return $component;
        }
    }

    throw new RuntimeException("No infolist entry named {$name}.");
}

it('registers the v3 resource on a panel via the plugin', function () {
    $panel = Panel::make()->id('testing');

    CookieConsentFilamentPlugin::make()->register($panel);

    expect($panel->getResources())->toContain(ConsentLogResource::class);
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('is a read-only resource on v3', function () {
    expect(ConsentLogResource::canCreate())->toBeFalse();
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('exposes an infolist with entries on v3', function () {
    $infolist = ConsentLogResource::infolist(Infolist::make(new ListConsentLogs));

    expect($infolist)->toBeInstanceOf(Infolist::class)
        ->and($infolist->getComponents())->not->toBeEmpty()
        ->and(v3ConsentLogInfolistEntryNames())->not->toBeEmpty();
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('shows in the v3 infolist the columns the table omits', function () {
    $names = v3ConsentLogInfolistEntryNames();

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
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('registers a view action on the v3 table', function () {
    $actions = v3ConsentLogTable()->getActions();

    expect($actions)->not->toBeEmpty();

    $classes = array_map(fn ($action): string => $action::class, $actions);

    expect($classes)->toContain(ViewAction::class);
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('registers no edit action on the v3 table', function () {
    $classes = array_map(fn ($action): string => $action::class, v3ConsentLogTable()->getActions());

    expect($classes)->not->toContain(EditAction::class);
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('formats accepted services readably on v3', function () {
    $formatted = ConsentLogInfolist::formatAcceptedServices([
        'necessary' => [],
        'analytics' => ['ga4'],
    ]);

    expect($formatted)->toBe('necessary: None · analytics: ga4')
        ->and($formatted)->not->toContain('Array');
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('wires the accepted services formatter to the v3 infolist entry', function () {
    $rendered = (string) v3ConsentLogInfolistEntry('accepted_services')->formatState([
        'necessary' => [],
        'analytics' => ['ga4'],
    ]);

    expect($rendered)->toContain('necessary: None')
        ->and($rendered)->toContain('analytics: ga4')
        ->and($rendered)->not->toContain('Array');
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('wires the payload pretty-printer to the v3 infolist entry', function () {
    $entry = v3ConsentLogInfolistEntry('payload');
    $state = ['categories' => ['necessary'], 'url' => 'https://example.com/a'];

    expect((string) $entry->formatState($state))->toContain("\n    ")
        ->and($entry->getCopyableState($state))->toContain("\n    ")
        ->and($entry->getCopyableState($state))->toContain('https://example.com/a');
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('copies the untruncated consent id from the v3 table', function () {
    $column = v3ConsentLogTable()->getColumn('consent_id');
    $uuid = '2f1c9f6e-0f2f-4f4a-9c1d-6d0d1f2a3b4c';

    expect($column->formatState($uuid))->not->toBe($uuid)
        ->and($column->getCopyableState($uuid))->toBe($uuid);
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');

it('pretty-prints the payload on v3', function () {
    $formatted = ConsentLogInfolist::formatPayload(['categories' => ['necessary'], 'url' => 'https://example.com/a']);

    expect($formatted)->toContain("\n    ")
        ->and($formatted)->toContain('https://example.com/a');
})->skip(FilamentVersion::usesSchemas() || ! FilamentVersion::isInstalled(), 'Filament v3 is not installed.');
