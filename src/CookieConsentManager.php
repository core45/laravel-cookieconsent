<?php

namespace Core45\CookieConsent;

use Core45\CookieConsent\Support\ConfigBuilder;

class CookieConsentManager
{
    public function __construct(protected ConfigBuilder $builder) {}

    /**
     * Sentinel converted to a real RegExp by the inline JS reviver.
     *
     * @return array{__regex__: string, flags: string}
     */
    public function regex(string $pattern, string $flags = ''): array
    {
        return ['__regex__' => $pattern, 'flags' => $flags];
    }

    /** @return array<string, mixed> */
    public function config(): array
    {
        return $this->builder->build();
    }
}
