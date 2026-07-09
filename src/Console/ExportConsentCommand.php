<?php

namespace Core45\CookieConsent\Console;

use Carbon\Carbon;
use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Console\Command;

class ExportConsentCommand extends Command
{
    protected $signature = 'cookieconsent:export
        {--user= : Filter by user id — matches user_id only unless --user-type is also given, so ids can collide across morph types}
        {--user-type= : Filter by user_type (morph class); combine with --user to scope the id to a specific morph type}
        {--consent-id= : Filter by orestbida consent id}
        {--from= : Only rows created on/after this date}
        {--to= : Only rows created on/before this date (date-only values are treated as end-of-day, so the whole day is included)}
        {--format=json : json (JSON lines) or csv}';

    protected $description = 'Export consent logs as evidence (JSON lines or CSV to stdout).';

    public function handle(): int
    {
        $format = (string) $this->option('format');

        if (! in_array($format, ['json', 'csv'], true)) {
            $this->error("Invalid --format \"{$format}\". Expected \"json\" or \"csv\".");

            return self::FAILURE;
        }

        $query = ConsentLog::query()->orderBy('id');

        if ($this->option('consent-id') !== null) {
            $query->forConsentId((string) $this->option('consent-id'));
        }

        if ($this->option('user') !== null) {
            $query->where('user_id', $this->option('user'));
        }

        if ($this->option('user-type') !== null) {
            $query->where('user_type', $this->option('user-type'));
        }

        if ($this->option('from') !== null || $this->option('to') !== null) {
            $to = $this->option('to') ?? now()->toDateTimeString();

            $query->between(
                $this->option('from') ?? '1970-01-01',
                static::inclusiveToBound($to),
            );
        }

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
                    $this->line(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                }
            }
        });

        return self::SUCCESS;
    }

    /**
     * Make a `--to` bound inclusive of the whole day when it has no time
     * component (e.g. `--to=2026-07-08`), so same-day rows aren't excluded
     * by an implicit `00:00:00`. Values that already carry a time are used
     * as given.
     */
    protected static function inclusiveToBound(string $to): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($to)) === 1) {
            return Carbon::parse($to)->endOfDay()->toDateTimeString();
        }

        return $to;
    }

    /** @param array<int|string, string> $fields */
    protected static function csvLine(array $fields): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_values($fields), ',', '"', '');
        rewind($handle);
        $line = rtrim((string) stream_get_contents($handle), "\n");
        fclose($handle);

        return $line;
    }
}
