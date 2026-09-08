<?php

namespace Core45\CookieConsent\Support;

class IframemanagerRenderer
{
    public function __construct(
        protected ScriptsRenderer $scripts,
        protected TranslationLoader $loader,
    ) {}

    public function render(): string
    {
        if (! config('cookieconsent.iframemanager.enabled', false)) {
            return '';
        }

        $services = [];
        $categoryMap = [];
        $translations = $this->loader->lines('iframemanager', app()->getLocale());

        foreach ((array) config('cookieconsent.iframemanager.services', []) as $name => $service) {
            $categoryMap[$name] = (string) ($service['category'] ?? 'necessary');
            unset($service['category']);

            $lines = $translations[$name] ?? null;

            if (is_array($lines)) {
                $service['languages'] = array_replace(
                    [app()->getLocale() => $lines],
                    (array) ($service['languages'] ?? []),
                );
            }

            $services[$name] = $service;
        }

        return view('cookieconsent::iframemanager', [
            'imConfig' => ['currLang' => app()->getLocale(), 'services' => $services],
            'categoryMap' => $categoryMap,
            'nonceAttr' => $this->scripts->nonceAttribute(),
        ])->render();
    }
}
