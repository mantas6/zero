<?php

namespace App\Commands\TimeEntries;

use App\TimeEntry;
use LaravelZero\Framework\Commands\Command;

class CurrentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:current {--with-project : Include project name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display current task name';

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

        $task = $runningEntry->task;
        $output = $task->name ?? '(unknown task)';

        if ($this->option('with-project') && $task?->project) {
            $output = $task->project->name . ': ' . $output;
        }

        $this->line($output);

        return self::SUCCESS;
    }
}
