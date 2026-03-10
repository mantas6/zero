<?php

namespace App\Commands\Tasks;

use App\Commands\Concerns\ResolvesProjectFilter;
use App\Project;
use App\Task;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\search;

class CopyCommand extends Command implements PromptsForMissingInput
{
    use ResolvesProjectFilter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cp {project-name} {--y|sync}';

    protected $aliases = ['cp'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy a task name to the clipboard';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('sync')) {
            $this->call(SyncCommand::class, [
                'project-name' => $this->argument('project-name'),
            ]);
        }

        $projectFilter = $this->warnIfProjectFilterInvalid();

        $query = Project::query()
            ->where('name', 'like', '%'.$this->argument('project-name').'%');

        if ($projectFilter instanceof Project) {
            $query->where('id', $projectFilter->id);
        }

        $project = $query->first();

        if (!$project) {
            $this->components->error('No project found matching "'.$this->argument('project-name').'".');

            return self::FAILURE;
        }

        $tasks = $project->tasks->reverse();

        if ($tasks->isEmpty()) {
            $this->components->warn("No tasks found for project \"{$project->name}\". Run tasks:sync first.");

            return self::FAILURE;
        }

        $taskId = search(
            label: 'Select a task to copy',
            options: fn (string $value) => $tasks
                ->filter(fn (Task $task): bool => mb_stripos($task->name, $value) !== false)
                ->mapWithKeys(fn (Task $task): array => [$task->id => $task->name])
                ->all(),
            placeholder: 'Search for a task...',
        );

        /** @var Task $task */
        $task = $tasks->firstOrFail(fn (Task $task): bool => $task->id === $taskId);

        Process::input($task->name)->run('xc');

        return self::SUCCESS;
    }
}
