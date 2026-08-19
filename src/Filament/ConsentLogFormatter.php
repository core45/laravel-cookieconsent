<?php

namespace Core45\CookieConsent\Filament;

/**
 * Presentation helpers shared by the Filament v3 and v4/v5 infolists.
 *
 * Deliberately free of any Filament import: it is loaded under every supported
 * major, so a reference to a class that only exists in one of them would be a
 * fatal error on the others.
 */
class ConsentLogFormatter
{
    /**
     * Render a `category => services` map readably. An empty inner array is the
     * normal case for a category that declares no named services.
     */
    public static function acceptedServices(mixed $state): string
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

    public static function payload(mixed $state): string
    {
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = json_last_error() === JSON_ERROR_NONE ? $decoded : $state;
        }

        return (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
