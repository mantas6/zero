<?php

namespace App\Commands\TimeEntries;

use App\Commands\Concerns\ResolvesProjectFilter;
use App\Project;
use App\TimeEntry;
use LaravelZero\Framework\Commands\Command;

class TotalCommand extends Command
{
    use ResolvesProjectFilter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:total';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display total tracked time for today';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectFilter = $this->warnIfProjectFilterInvalid();

        $query = TimeEntry::query()->whereToday();

        if ($projectFilter instanceof Project) {
            $query->forProject($projectFilter);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            $this->components->info('No time entries for today.');

            return self::SUCCESS;
        }

        $totalSeconds = 0;

        foreach ($entries as $entry) {
            $start = $entry->started_at;
            $stop = $entry->stopped_at ?? now();

            if ($start) {
                $totalSeconds += (int) $start->diffInSeconds($stop);
            }
        }

        $this->line($this->formatDuration($totalSeconds));

        return self::SUCCESS;
    }

    /**
     * Format seconds into a human-readable duration string (e.g., "4h 32m").
     */
    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        if ($minutes > 0) {
            return "{$minutes}m";
        }

        return "{$seconds}s";
    }
}
