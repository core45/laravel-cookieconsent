<?php

namespace Core45\CookieConsent;

use Core45\CookieConsent\Support\ConfigBuilder;
use Core45\CookieConsent\Support\Consent;

class CookieConsentManager
{
    public function __construct(protected ConfigBuilder $builder, protected Consent $consent) {}

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

    public function policyHash(): string
    {
        return $this->builder->policyHash();
    }

    public function has(string $category): bool
    {
        return $this->consent->has($category);
    }

    /** @return list<string> */
    public function categories(): array
    {
        return $this->consent->categories();
    }

    public function acceptType(): ?string
    {
        return $this->consent->acceptType();
    }
}
