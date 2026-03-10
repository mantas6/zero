<?php

namespace App\Http\Integrations\Toggl\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class TasksRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceId,
        protected readonly string|int $projectId,
        protected readonly ?bool $active = null,
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/workspaces/'.$this->workspaceId.'/projects/'.$this->projectId.'/tasks';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        if ($this->active === null) {
            return [];
        }

        return [
            'active' => $this->active ? 'true' : 'false',
        ];
    }
}
