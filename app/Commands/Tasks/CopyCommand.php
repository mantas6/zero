<?php

namespace App\Commands\Tasks;

use App\Project;
use App\Task;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\search;

class CopyCommand extends Command implements PromptsForMissingInput
{
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->option('sync')) {
            $this->call(SyncCommand::class, [
                'project-name' => $this->argument('project-name'),
            ]);
        }

        $tasks = Project::query()
            ->where('name', 'like', '%'.$this->argument('project-name').'%')
            ->firstOrFail()
            ->tasks
            ->reverse();

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
    }
}
