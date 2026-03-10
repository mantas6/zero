<?php

namespace App\Commands\TimeEntries;

use App\TimeEntry;
use LaravelZero\Framework\Commands\Command;

class ElapsedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:elapsed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display elapsed time for the current timer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $runningEntry = TimeEntry::query()
            ->whereToday()
            ->whereNull('stopped_at')
            ->first();

        if (!$runningEntry) {
            $this->components->warn('No timer is currently running.');

            return self::FAILURE;
        }

        $seconds = (int) $runningEntry->started_at->diffInSeconds(now());

        $this->line($this->formatDuration($seconds));

        return self::SUCCESS;
    }

    /**
     * Format seconds into a human-readable duration string (e.g., "2h 15m").
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
