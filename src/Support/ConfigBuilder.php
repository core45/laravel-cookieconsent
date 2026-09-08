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

        $config['language']['translations'] = $this->dropOrphanSections(
            (array) ($config['language']['translations'] ?? []),
            array_map(strval(...), array_keys((array) ($config['categories'] ?? [])))
        );

        return $config;
    }

    /**
     * Remove preferencesModal sections whose linkedCategory is not configured.
     *
     * Laravel merges a published vendor lang file over the package's with
     * array_replace_recursive, so a consumer who trims `categories` cannot
     * delete the package's trailing section entries from the lang file.
     * Runs after the inline `language.translations` merge and before
     * policyHash() so the hashed payload matches what the browser renders.
     *
     * @param  array<array-key, mixed>  $translations
     * @param  list<string>  $categories
     * @return array<array-key, mixed>
     */
    protected function dropOrphanSections(array $translations, array $categories): array
    {
        $linked = function (mixed $section) use ($categories): bool {
            if (! is_array($section) || ! isset($section['linkedCategory'])) {
                return true;
            }

            $category = $section['linkedCategory'];

            return (is_string($category) || is_int($category))
                && in_array((string) $category, $categories, true);
        };

        foreach ($translations as $locale => $lines) {
            $modal = is_array($lines) ? ($lines['preferencesModal'] ?? null) : null;
            $sections = is_array($modal) ? ($modal['sections'] ?? null) : null;

            if (! is_array($modal) || ! is_array($sections)) {
                continue;
            }

            $modal['sections'] = array_values(array_filter($sections, $linked));

            $lines['preferencesModal'] = $modal;
            $translations[$locale] = $lines;
        }

        return $translations;
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
