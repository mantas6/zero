<?php

namespace App\Http\Integrations\Toggl\Requests;

use App\TimeEntry;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateEntryRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $workspaceId,
        private readonly TimeEntry $entry,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/workspaces/' . $this->workspaceId . '/time_entries';
    }

    protected function defaultBody(): array
    {
        $startedAt = $this->entry->started_at;
        $stoppedAt = $this->entry->stopped_at;
        $task = $this->entry->task;
        $project = $task?->project;

        $body = [
            'billable' => $project !== null ? $project->billable : true,
            'created_with' => 'zero-cli',
            'description' => (string) $task?->name,
            'duration' => $startedAt && $stoppedAt
                ? (int) $startedAt->diffInSeconds($stoppedAt)
                : -1,
            'project_id' => $project?->ext_id,
            'start' => $startedAt?->utc()->toIso8601String(),
            'stop' => $stoppedAt?->utc()->toIso8601String(),
            'task_id' => $task?->ext_id,
            'workspace_id' => (int) $this->workspaceId,
        ];

        $tags = $project !== null ? $project->tags : [];

        if ($tags !== []) {
            $body['tags'] = $tags;
        }

        return $body;
    }
}
