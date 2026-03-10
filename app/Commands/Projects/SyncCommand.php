<?php

namespace App\Commands\Projects;

use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:sync {--active : Only sync active projects from Toggl}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync projects from Toggl workspace into the local database';

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

        $activeOnly = $this->option('active');

        /** @var Collection<int, array{id: int, name: string, active: bool, billable: bool|null, color: string, client_name: string|null}> $items */
        $items = $connector->projects()
            ->collect();

        if ($activeOnly) {
            $items = $items->where('active', true);
        }

        if ($items->isEmpty()) {
            $this->components->warn('No projects found in your Toggl workspace.');

            return self::FAILURE;
        }

        $synced = 0;

        foreach ($items as $item) {
            $project = Project::query()
                ->firstOrNew(['ext_id' => $item['id']]);

            $project->name = $item['name'];
            $project->ext_id = $item['id'];
            $project->active = $item['active'];
            $project->billable = $item['billable'] ?? false;
            $project->color = $item['color'];
            $project->client_name = $item['client_name'];
            $project->save();

            $synced++;

            $this->components->twoColumnDetail(
                $item['name'],
                $project->wasRecentlyCreated ? '<fg=green>added</>' : '<fg=yellow>updated</>'
            );
        }

        $this->newLine();
        $this->components->info("Synced {$synced} projects from Toggl.");

        return self::SUCCESS;
    }
}
