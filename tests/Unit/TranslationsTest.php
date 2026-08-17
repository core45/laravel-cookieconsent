<?php

/**
 * Structural guard for the shipped locales. Every published locale must expose
 * exactly the keys the English reference does, so a half-finished translation
 * cannot silently ship a banner with missing buttons.
 */
function langPath(string ...$segments): string
{
    return implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'resources', 'lang', ...$segments]);
}

/**
 * @return array<string, string>
 */
function flattenTranslations(array $translations, string $prefix = ''): array
{
    $flat = [];

    foreach ($translations as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

        if (is_array($value)) {
            $flat += flattenTranslations($value, $path);

            continue;
        }

        $flat[$path] = $value;
    }

    return $flat;
}

/**
 * @return array<int, string>
 */
function publishedLocales(): array
{
    $locales = array_map('basename', glob(langPath('*'), GLOB_ONLYDIR) ?: []);
    sort($locales);

    return $locales;
}

it('publishes both translation files for every locale', function () {
    $locales = publishedLocales();

    expect($locales)->toContain('en');

    foreach ($locales as $locale) {
        expect(langPath($locale, 'cookieconsent.php'))->toBeFile()
            ->and(langPath($locale, 'iframemanager.php'))->toBeFile();
    }
});

it('keeps every locale key-identical to the English reference', function (string $file) {
    $reference = array_keys(flattenTranslations(require langPath('en', $file)));
    sort($reference);

    foreach (publishedLocales() as $locale) {
        $keys = array_keys(flattenTranslations(require langPath($locale, $file)));
        sort($keys);

        expect($keys)->toBe($reference, "[$locale/$file] does not match the English key set.");
    }
})->with(['cookieconsent.php', 'iframemanager.php']);

it('leaves no translation value empty', function (string $file) {
    foreach (publishedLocales() as $locale) {
        foreach (flattenTranslations(require langPath($locale, $file)) as $key => $value) {
            expect($value)->toBeString("[$locale/$file] $key is not a string.")
                ->and(trim($value))->not->toBe('', "[$locale/$file] $key is empty.");
        }
    }
})->with(['cookieconsent.php', 'iframemanager.php']);
