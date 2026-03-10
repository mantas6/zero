<?php

namespace App\Commands\TimeEntries;

use App\Commands\Concerns\ResolvesProjectFilter;
use App\Project;
use App\Task;
use App\TimeEntry;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class StartCommand extends Command
{
    use ResolvesProjectFilter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:start {task-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a timer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $taskId = $this->argument('task-id');

        if (!is_numeric($taskId)) {
            $this->components->error('Task ID must be a number.');

            return self::FAILURE;
        }

        $task = Task::find((int) $taskId);

        if (!$task) {
            $this->components->error("Task with ID {$taskId} not found.");

            return self::FAILURE;
        }

        $projectFilter = $this->warnIfProjectFilterInvalid();

        if ($projectFilter instanceof Project && $task->project_id !== $projectFilter->id) {
            $this->components->error("Task \"{$task->name}\" does not belong to project \"{$projectFilter->name}\".");

            return self::FAILURE;
        }

        $runningEntry = TimeEntry::query()
            ->whereToday()
            ->whereNull('stopped_at')
            ->first();

        if ($runningEntry) {
            $runningEntry->update([
                'stopped_at' => now(),
            ]);
        }

        TimeEntry::create([
            'started_at' => now(),
            'task_id' => $task->id,
        ]);

        $this->components->info("Timer started for: {$task->name}");

        return self::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
