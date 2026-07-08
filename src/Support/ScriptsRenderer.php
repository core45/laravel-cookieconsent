<?php

namespace Core45\CookieConsent\Support;

class ScriptsRenderer
{
    public function __construct(protected ConfigBuilder $builder) {}

    public function render(): string
    {
        $loggingEnabled = (bool) config('cookieconsent.logging.enabled', true);

        return view('cookieconsent::scripts', [
            'config' => $this->builder->build(),
            'nonceAttr' => $this->nonceAttribute(),
            'loggingEnabled' => $loggingEnabled,
            'csrfToken' => $loggingEnabled && config('cookieconsent.logging.csrf', true) ? csrf_token() : null,
            'logUrl' => $loggingEnabled ? url('cookie-consent/log') : null,
        ])->render();
    }

    public function nonceAttribute(): string
    {
        $resolver = config('cookieconsent.csp_nonce');

        return is_callable($resolver) ? ' nonce="'.e($resolver()).'"' : '';
    }
}
