<?php

namespace Core45\CookieConsent\Support;

class IframeTag
{
    /** @param array<string, string|bool> $options */
    public static function render(string $service, string $id, array $options = []): string
    {
        $html = '<div data-service="'.e($service).'" data-id="'.e($id).'"';

        foreach (['params', 'thumbnail', 'ratio'] as $option) {
            if (isset($options[$option])) {
                $html .= ' data-'.$option.'="'.e((string) $options[$option]).'"';
            }
        }

        if (! empty($options['autoscale'])) {
            $html .= ' data-autoscale';
        }

        return $html.'></div>';
    }
}
