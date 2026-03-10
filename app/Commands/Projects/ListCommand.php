<?php

namespace App\Commands\Projects;

use App\Project;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:list {--all : Include inactive (archived) projects}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List locally synced projects';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Project::query();

        if (!$this->option('all')) {
            $query->whereActive();
        }

        $projects = $query->orderBy('name')->get();

        if ($projects->isEmpty()) {
            $this->components->warn('No local projects found. Run projects:sync or projects:add first.');

            return self::SUCCESS;
        }

        foreach ($projects as $project) {
            $detail = collect();

            if ($project->client_name) {
                $detail->push($project->client_name);
            }

            if (!$project->active) {
                $detail->push('<fg=gray>archived</>');
            }

            $this->components->twoColumnDetail(
                $project->name,
                $detail->implode(' · ')
            );
        }

        return self::SUCCESS;
    }
}
