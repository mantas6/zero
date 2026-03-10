<?php

namespace App\Commands\Tasks;

use App\Commands\Concerns\ResolvesProjectFilter;
use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaravelZero\Framework\Commands\Command;

class SyncCommand extends Command
{
    use ResolvesProjectFilter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:sync {project-name?}';

    protected $aliases = ['sync', 'y'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync tasks from Toggl for local projects';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectFilter = $this->warnIfProjectFilterInvalid();

        if ($projectName = $this->argument('project-name')) {
            $projects = Project::query()
                ->where('name', 'like', '%' . $projectName . '%')
                ->get();
        } elseif ($projectFilter) {
            $projects = Project::query()
                ->where('id', $projectFilter->id)
                ->get();
        } else {
            $projects = Project::all();
        }

        if ($projects->isEmpty()) {
            $this->components->warn('No projects found. Add a project first with projects:add.');

            return self::FAILURE;
        }

        try {
            $connector = new TogglConnector;
        } catch (ModelNotFoundException) {
            $this->components->error('Not authenticated. Run the authenticate command first.');

            return self::FAILURE;
        }

        foreach ($projects as $project) {
            $connector
                ->tasks($project)
                ->collect()
                ->each(function (array $item) use ($project): void {
                    $task = $project->tasks()
                        ->firstOrNew(['ext_id' => $item['id']]);

                    $task->name = $item['name'];
                    $task->save();
                })
                ->tap(fn ($items) => $this->components->twoColumnDetail($project->name, (string) count($items)));
        }

        return self::SUCCESS;
    }
}
