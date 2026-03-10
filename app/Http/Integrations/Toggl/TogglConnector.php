<?php

namespace App\Http\Integrations\Toggl;

use App\Http\Integrations\Toggl\Requests\CreateEntryRequest;
use App\Http\Integrations\Toggl\Requests\ProjectsRequest;
use App\Http\Integrations\Toggl\Requests\TagsRequest;
use App\Http\Integrations\Toggl\Requests\TasksRequest;
use App\Http\Integrations\Toggl\Requests\UpdateEntryRequest;
use App\Project;
use App\TimeEntry;
use App\Token;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class TogglConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    protected string $token;

    protected string $workspaceId;

    public function __construct(string $tokenString = '')
    {
        if ($tokenString) {
            $this->token = $tokenString;

            return;
        }

        $token = Token::query()
            ->where('name', 'default')
            ->firstOrFail();

        $this->workspaceId = $token->default_workspace_id;
        $this->token = $token->contents;
    }

    public function projects(): Response
    {
        return $this->send(
            new ProjectsRequest($this->workspaceId)
        );
    }

    public function tags(): Response
    {
        return $this->send(
            new TagsRequest($this->workspaceId)
        );
    }

    public function tasks(Project $project, ?bool $active = null): Response
    {
        return $this->send(
            new TasksRequest($this->workspaceId, $project->ext_id, $active)
        );
    }

    public function createEntry(TimeEntry $entry): Response
    {
        return $this->send(
            new CreateEntryRequest($this->workspaceId, $entry)
        );
    }

    public function updateEntry(TimeEntry $entry): Response
    {
        return $this->send(
            new UpdateEntryRequest($this->workspaceId, $entry)
        );
    }

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.track.toggl.com/api/v9';
    }

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator($this->token, 'api_token');
    }
}
