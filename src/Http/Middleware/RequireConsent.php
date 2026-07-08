<?php

namespace Core45\CookieConsent\Http\Middleware;

use Closure;
use Core45\CookieConsent\Support\Consent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireConsent
{
    public function __construct(protected Consent $consent) {}

    public function handle(Request $request, Closure $next, string $category): Response
    {
        abort_unless($this->consent->has($category), 403, "Consent for [{$category}] has not been given.");

        return $next($request);
    }
}
