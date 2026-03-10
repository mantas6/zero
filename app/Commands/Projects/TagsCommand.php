<?php

namespace App\Commands\Projects;

use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;

class TagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure tags to inherit when pushing time entries for a project';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projects = Project::query()->whereActive()->orderBy('name')->get();

        if ($projects->isEmpty()) {
            $this->components->warn('No local projects found. Run projects:sync or projects:add first.');

            return self::FAILURE;
        }

        $selectedProjectId = search(
            label: 'Select a project to configure tags for',
            options: fn (string $value) => $projects
                ->filter(fn (Project $project): bool => mb_stripos($project->name, $value) !== false)
                ->mapWithKeys(fn (Project $project): array => [$project->id => $project->name])
                ->all(),
            placeholder: 'Search for a project...',
        );

        if (!$selectedProjectId) {
            return self::SUCCESS;
        }

        /** @var Project $project */
        $project = $projects->firstOrFail(fn (Project $p): bool => $p->id === (int) $selectedProjectId);

        try {
            $connector = new TogglConnector;
        } catch (ModelNotFoundException) {
            $this->components->error('Not authenticated. Run the authenticate command first.');

            return self::FAILURE;
        }

        /** @var Collection<int, array{id: int, name: string}> $workspaceTags */
        $workspaceTags = $connector->tags()->collect();

        if ($workspaceTags->isEmpty()) {
            $this->components->warn('No tags found in your Toggl workspace.');

            return self::FAILURE;
        }

        $currentTags = $project->tags ?? [];

        $tagOptions = $workspaceTags
            ->mapWithKeys(fn (array $tag): array => [$tag['name'] => $tag['name']])
            ->all();

        /** @var array<int, string> $selectedTags */
        $selectedTags = multiselect(
            label: "Select tags for \"{$project->name}\"",
            options: $tagOptions,
            default: array_intersect($currentTags, array_keys($tagOptions)),
            hint: 'Use space to toggle, enter to confirm',
        );

        $project->tags = array_values($selectedTags);
        $project->save();

        if (empty($selectedTags)) {
            $this->components->info("Cleared tags for \"{$project->name}\".");
        } else {
            $this->components->info("Set tags for \"{$project->name}\": " . implode(', ', $selectedTags));
        }

        return self::SUCCESS;
    }
}
