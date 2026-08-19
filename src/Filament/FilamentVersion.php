<?php

namespace Core45\CookieConsent\Filament;

use Composer\InstalledVersions;
use Core45\CookieConsent\Filament\Resources\ConsentLogResource;
use Core45\CookieConsent\Filament\V3\Resources\ConsentLogResource as ConsentLogResourceV3;

/**
 * Detects which Filament major is installed so the plugin can register the
 * matching ConsentLog resource.
 *
 * Filament v4 moved infolists onto the new `Filament\Schemas\Schema` API and
 * renamed the table row-action method. The signature of `Resource::infolist()`
 * changed with it, so a resource written for one side of that break is a fatal
 * error at *class load* time on the other, not a recoverable runtime failure.
 * That is why the branch happens here, on class names, and why the two resource
 * trees never reference each other.
 */
class FilamentVersion
{
    /** Panel class; present in every major this package supports. */
    private const PANEL = 'Filament\Panel';

    /** Introduced in v4. The one break that separates v3 from v4 and v5. */
    private const SCHEMA = 'Filament\Schemas\Schema';

    /** Checked in order; the monorepo keeps their versions in lockstep. */
    private const PACKAGES = ['filament/filament', 'filament/support'];

    public static function isInstalled(): bool
    {
        return class_exists(self::PANEL);
    }

    /**
     * True when the installed Filament builds infolists from schemas (v4+),
     * false on v3. This is the discriminator the resource choice turns on.
     */
    public static function usesSchemas(): bool
    {
        return class_exists(self::SCHEMA);
    }

    /**
     * Installed major, or null when Filament is absent.
     */
    public static function major(): ?int
    {
        if (! self::isInstalled()) {
            return null;
        }

        $version = self::version();

        if ($version !== null && preg_match('/^v?(\d+)\./', $version, $matches) === 1) {
            return (int) $matches[1];
        }

        // Composer metadata is unavailable (a non-Composer autoloader, or a
        // fork installed under another name). Fall back to the class probe,
        // which cannot tell v4 from v5 but does answer the question that
        // matters for picking a resource.
        return self::usesSchemas() ? 4 : 3;
    }

    /**
     * Installed version string as Composer reports it, or null when Filament is
     * absent or its metadata cannot be read.
     */
    public static function version(): ?string
    {
        if (! self::isInstalled() || ! class_exists(InstalledVersions::class)) {
            return null;
        }

        foreach (self::PACKAGES as $package) {
            if (InstalledVersions::isInstalled($package)) {
                return InstalledVersions::getPrettyVersion($package);
            }
        }

        return null;
    }

    /**
     * The ConsentLog resource matching the installed Filament.
     *
     * `::class` resolves at compile time and does not autoload, so naming both
     * classes here is safe even though only one of them can be loaded.
     *
     * @return class-string
     */
    public static function resourceClass(): string
    {
        return self::usesSchemas()
            ? ConsentLogResource::class
            : ConsentLogResourceV3::class;
    }
}
