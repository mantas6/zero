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
        protected readonly string $workspaceId, // todo: resolve in class
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

        return [
            'billable' => true,
            'created_with' => 'zero-cli',
            'duration' => $startedAt && $stoppedAt
                ? $startedAt->diffInSeconds($stoppedAt)
                : -1,
            'start' => $startedAt?->toIso8601String(),
            'task_id' => $this->entry->task_id ?? null,
        ];
    }
}
