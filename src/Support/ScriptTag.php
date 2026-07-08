<?php

namespace Core45\CookieConsent\Support;

class ScriptTag
{
    /** @param array<string, string> $attributes */
    public static function open(string $category, ?string $src = null, array $attributes = []): string
    {
        $html = '<script type="text/plain" data-category="'.e($category).'"';

        foreach ($attributes as $name => $value) {
            $html .= ' '.e($name).'="'.e($value).'"';
        }

        if ($src !== null) {
            return $html.' data-src="'.e($src).'"></script>';
        }

        return $html.'>';
    }
}
