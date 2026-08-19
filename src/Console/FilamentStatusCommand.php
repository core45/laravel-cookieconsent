<?php

namespace Core45\CookieConsent\Console;

use Core45\CookieConsent\Filament\CookieConsentFilamentPlugin;
use Core45\CookieConsent\Filament\FilamentVersion;
use Illuminate\Console\Command;

class FilamentStatusCommand extends Command
{
    protected $signature = 'cookieconsent:filament';

    protected $description = 'Report which Filament version is installed and which consent-log resource it will use.';

    public function handle(): int
    {
        if (! FilamentVersion::isInstalled()) {
            $this->warn('Filament is not installed. The consent-log admin resource is unavailable.');
            $this->line('Install it with: composer require filament/filament');

            return self::SUCCESS;
        }

        $major = FilamentVersion::major();
        $version = FilamentVersion::version();

        // Plain lines rather than components->twoColumnDetail(): that pads to the
        // terminal width and truncates long values, so the resource class would
        // render differently on a narrow console and disappear under a mocked
        // output buffer, which is where this command gets asserted.
        $this->detail('Filament version', $version ?? 'unknown (Composer metadata unavailable)');
        $this->detail('Detected major', 'v'.$major);
        $this->detail('Infolist API', FilamentVersion::usesSchemas() ? 'schemas (v4+)' : 'infolists (v3)');
        $this->detail('Resource in use', FilamentVersion::resourceClass());

        if ($major !== null && ($major < 3 || $major > 5)) {
            $this->newLine();
            $this->warn("Filament v{$major} is outside the supported range (v3, v4, v5). The resource above is a best guess and may not load.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Register it in your panel provider:');
        $this->newLine();
        $this->line('    use '.CookieConsentFilamentPlugin::class.';');
        $this->newLine();
        $this->line('    $panel->plugin(CookieConsentFilamentPlugin::make());');
        $this->newLine();
        $this->line('The plugin picks the matching resource itself; no version argument is needed.');

        return self::SUCCESS;
    }

    private function detail(string $label, string $value): void
    {
        $this->line(sprintf('  %s  %s', str_pad($label, 16, '.'), $value));
    }
}
