<?php

namespace Core45\CookieConsent\Support;

use Illuminate\Support\Arr;

class ConfigBuilder
{
    /** @var list<string> Package-only keys never passed to the JS config. */
    public const PACKAGE_KEYS = ['translations_mode', 'csp_nonce', 'logging', 'iframemanager'];

    /** @return array<string, mixed> */
    public function build(): array
    {
        $config = Arr::except((array) config('cookieconsent', []), self::PACKAGE_KEYS);

        $config['language'] = array_replace_recursive([
            'default' => app()->getLocale(),
            'translations' => $this->translations(),
        ], (array) ($config['language'] ?? []));

        return $config;
    }

    /** @return array<string, array<string, mixed>> */
    protected function translations(): array
    {
        $locales = config('cookieconsent.translations_mode', 'active') === 'all'
            ? $this->availableLocales()
            : [app()->getLocale()];

        $translations = [];

        foreach ($locales as $locale) {
            $lines = trans('cookieconsent::cookieconsent', [], $locale);

            if (is_array($lines)) {
                $translations[$locale] = $lines;
            }
        }

        return $translations;
    }

    /** @return list<string> */
    protected function availableLocales(): array
    {
        $locales = [];

        foreach ([__DIR__.'/../../resources/lang', lang_path('vendor/cookieconsent')] as $dir) {
            if (is_dir($dir)) {
                foreach (glob($dir.'/*', GLOB_ONLYDIR) ?: [] as $localeDir) {
                    $locales[] = basename($localeDir);
                }
            }
        }

        return array_values(array_unique($locales));
    }

    public function policyHash(): string
    {
        return hash(
            'sha256',
            static::canonicalJson($this->build()).(string) config('cookieconsent.logging.policy_version')
        );
    }

    /** @param array<array-key, mixed> $value */
    public static function canonicalJson(array $value): string
    {
        return json_encode(
            static::sortRecursively($value),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @return array<array-key, mixed>
     */
    protected static function sortRecursively(array $value): array
    {
        if (! array_is_list($value)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = static::sortRecursively($item);
            }
        }

        return $value;
    }
}
