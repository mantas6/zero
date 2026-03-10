<?php

namespace App\Commands\Projects;

use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\search;

class AddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:add';

    protected $aliases = ['add'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a Toggl project to the local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $connector = new TogglConnector;
        } catch (ModelNotFoundException) {
            $this->components->error('Not authenticated. Run the authenticate command first.');

            return self::FAILURE;
        }

        /** @var Collection<int, array{id: int, name: string}> $items */
        $items = $connector->projects()
            ->collect();

        if ($items->isEmpty()) {
            $this->components->warn('No projects found in your Toggl workspace.');

            return self::FAILURE;
        }

        $selectedId = search(
            label: 'Select a project to add',
            options: fn (string $value) => $items
                ->filter(fn (array $project): bool => mb_stripos($project['name'], $value) !== false)
                ->mapWithKeys(fn (array $project): array => [$project['id'] => $project['name']])
                ->all(),
            placeholder: 'Search for a project...',
        );

        if (!$selectedId) {
            return self::SUCCESS;
        }

        $selected = $items->firstOrFail(fn (array $project): bool => $project['id'] === $selectedId);

        $project = Project::query()
            ->firstOrNew(['name' => $selected['name']]);

        $project->ext_id = $selected['id'];
        $project->save();

        $this->components->info("Project \"{$selected['name']}\" added.");

        return self::SUCCESS;
    }
}
