<?php

namespace App\Commands\Projects;

use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Collection<int, array{id: int, name: string}> $items */
        $items = (new TogglConnector)->projects()
            ->collect();

        $selectedId = search(
            label: 'Select a project to add',
            options: fn (string $value) => $items
                ->filter(fn (array $project): bool => mb_stripos($project['name'], $value) !== false)
                ->mapWithKeys(fn (array $project): array => [$project['id'] => $project['name']])
                ->all(),
            placeholder: 'Search for a project...',
        );

        if (!$selectedId) {
            return;
        }

        $selected = $items->firstOrFail(fn (array $project): bool => $project['id'] === $selectedId);

        $project = Project::query()
            ->firstOrNew(['name' => $selected['name']]);

        $project->ext_id = $selected['id'];
        $project->save();
    }
}
