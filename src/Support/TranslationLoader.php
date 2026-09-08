<?php

namespace Core45\CookieConsent\Support;

use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

/**
 * Loads one of the package's translation groups for a locale.
 *
 * In the default `merge` mode this is a thin wrapper around trans(), so a
 * published lang/vendor/cookieconsent file is merged over the package's with
 * array_replace_recursive, exactly as Laravel does for any package. That merge
 * cannot shorten a list or drop a key, which is why `replace` mode exists: the
 * first published file found in the translator's lang paths is used wholesale,
 * and the package file is only consulted when no published file exists.
 */
class TranslationLoader
{
    public const MERGE = 'merge';

    public const REPLACE = 'replace';

    public function __construct(protected Translator $translator) {}

    /**
     * A whole group, or one top-level key of it when $key is given.
     *
     * In merge mode the key is passed to the translator as a dotted item so
     * Laravel's per-key fallback-locale lookup applies exactly as it would for
     * a direct trans() call. In replace mode the file is the whole truth, so a
     * missing key is simply missing.
     *
     * @return array<array-key, mixed>
     */
    public function lines(string $group, string $locale, ?string $key = null): array
    {
        if (config('cookieconsent.translations_override', self::MERGE) !== self::REPLACE) {
            $item = 'cookieconsent::'.$group.($key === null ? '' : '.'.$key);
            $lines = $this->translator->get($item, [], $locale);

            return is_array($lines) ? $lines : [];
        }

        foreach ($this->localesToTry($locale) as $candidate) {
            foreach ($this->candidateFiles($group, $candidate) as $file) {
                if (! is_file($file)) {
                    continue;
                }

                $lines = require $file;

                if (! is_array($lines)) {
                    continue;
                }

                if ($key !== null) {
                    $lines = $lines[$key] ?? null;
                }

                return is_array($lines) ? $lines : [];
            }
        }

        return [];
    }

    /**
     * The requested locale first, then the app fallback locale, mirroring
     * Translator::get() so `replace` mode degrades the same way `merge` does.
     *
     * @return list<string>
     */
    protected function localesToTry(string $locale): array
    {
        $fallback = config('app.fallback_locale');

        return is_string($fallback) && $fallback !== '' && $fallback !== $locale
            ? [$locale, $fallback]
            : [$locale];
    }

    /**
     * Published override paths in the order the translator checks them,
     * followed by the package's own file.
     *
     * @return list<string>
     */
    protected function candidateFiles(string $group, string $locale): array
    {
        // The locale becomes a path segment that is then require()d, so
        // anything that is not a plain locale code is not looked up at all.
        if (preg_match('/^[A-Za-z0-9_-]+$/', $locale) !== 1) {
            return [];
        }

        $files = [];

        foreach ($this->overridePaths() as $path) {
            $files[] = "{$path}/vendor/cookieconsent/{$locale}/{$group}.php";
        }

        $files[] = __DIR__."/../../resources/lang/{$locale}/{$group}.php";

        return $files;
    }

    /**
     * The same lang paths FileLoader::loadNamespaceOverrides() walks, so a
     * path registered with Translator::addPath() is honoured in both modes.
     *
     * @return list<string>
     */
    public function overridePaths(): array
    {
        $loader = $this->translator->getLoader();

        if ($loader instanceof FileLoader) {
            return array_values(array_filter($loader->paths(), 'is_string'));
        }

        return [lang_path()];
    }
}
