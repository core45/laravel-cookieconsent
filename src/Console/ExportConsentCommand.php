<?php

namespace Core45\CookieConsent\Console;

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Console\Command;

class ExportConsentCommand extends Command
{
    protected $signature = 'cookieconsent:export
        {--user= : Filter by user id}
        {--consent-id= : Filter by orestbida consent id}
        {--from= : Only rows created on/after this date}
        {--to= : Only rows created on/before this date}
        {--format=json : json (JSON lines) or csv}';

    protected $description = 'Export consent logs as evidence (JSON lines or CSV to stdout).';

    public function handle(): int
    {
        $query = ConsentLog::query()->orderBy('id');

        if ($this->option('consent-id') !== null) {
            $query->forConsentId((string) $this->option('consent-id'));
        }

        if ($this->option('user') !== null) {
            $query->where('user_id', $this->option('user'));
        }

        if ($this->option('from') !== null || $this->option('to') !== null) {
            $query->between(
                $this->option('from') ?? '1970-01-01',
                $this->option('to') ?? now()->toDateTimeString(),
            );
        }

        $format = (string) $this->option('format');
        $headerWritten = false;

        $query->chunkById(500, function ($logs) use ($format, &$headerWritten): void {
            foreach ($logs as $log) {
                $row = $log->toArray();

                if ($format === 'csv') {
                    if (! $headerWritten) {
                        $this->line(static::csvLine(array_keys($row)));
                        $headerWritten = true;
                    }
                    $this->line(static::csvLine(array_map(
                        fn ($value) => is_array($value) ? json_encode($value) : (string) $value,
                        $row
                    )));
                } else {
                    $this->line(json_encode($row, JSON_UNESCAPED_UNICODE));
                }
            }
        });

        return self::SUCCESS;
    }

    /** @param array<int|string, string> $fields */
    protected static function csvLine(array $fields): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_values($fields));
        rewind($handle);
        $line = rtrim((string) stream_get_contents($handle), "\n");
        fclose($handle);

        return $line;
    }
}
