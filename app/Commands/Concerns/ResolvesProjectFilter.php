<?php

namespace App\Commands\Concerns;

use App\Project;

trait ResolvesProjectFilter
{
    /**
     * Resolve the project filter from the TG_PROJECT_ID configuration value.
     */
    protected function resolveProjectFilter(): ?Project
    {
        $projectId = config('app.project_id');

        if ($projectId === null || $projectId === '') {
            return null;
        }

        return Project::find((int) $projectId);
    }

    /**
     * Resolve the project filter, warning the user if the ID is invalid.
     */
    protected function warnIfProjectFilterInvalid(): ?Project
    {
        $projectId = config('app.project_id');

        if ($projectId === null || $projectId === '') {
            return null;
        }

        $project = Project::find((int) $projectId);

        if (!$project) {
            $this->components->warn("TG_PROJECT_ID={$projectId} does not match any local project. Filter ignored.");

            return null;
        }

        return $project;
    }
}
