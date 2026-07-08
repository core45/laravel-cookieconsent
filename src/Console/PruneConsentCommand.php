<?php

namespace Core45\CookieConsent\Console;

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Console\Command;

class PruneConsentCommand extends Command
{
    protected $signature = 'cookieconsent:prune';

    protected $description = 'Delete consent logs older than logging.retention_days (no-op when null).';

    public function handle(): int
    {
        $days = config('cookieconsent.logging.retention_days');

        if ($days === null) {
            $this->info('Retention is disabled (logging.retention_days is null); nothing pruned.');

            return self::SUCCESS;
        }

        $deleted = ConsentLog::query()
            ->where('created_at', '<', now()->subDays((int) $days))
            ->delete();

        $this->info("Pruned {$deleted} consent log(s).");

        return self::SUCCESS;
    }
}
