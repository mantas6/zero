<?php

namespace App\Commands\TimeEntries;

use App\TimeEntry;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List time entries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $entries = TimeEntry::query()
            ->whereToday()
            ->get();

        if ($entries->isEmpty()) {
            $this->components->info('No time entries for today.');

            return self::SUCCESS;
        }

        $rows = $entries
            ->map(fn (TimeEntry $entry): array => [
                $entry->task->name ?? '(unknown task)',
                $entry->started_at?->format('H:i'),
                $entry->stopped_at?->format('H:i'),
                $entry->started_at?->diffForHumans($entry->stopped_at ?: now()) ?? '',
            ])
            ->all();

        $this->table(
            headers: [],
            rows: $rows,
            tableStyle: 'compact',
        );

        return self::SUCCESS;
    }
}
