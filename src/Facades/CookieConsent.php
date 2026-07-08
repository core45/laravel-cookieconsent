<?php

namespace Core45\CookieConsent\Facades;

use Core45\CookieConsent\CookieConsentManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array{__regex__: string, flags: string} regex(string $pattern, string $flags = '')
 * @method static array config()
 *
 * @see CookieConsentManager
 */
class CookieConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CookieConsentManager::class;
    }
}
