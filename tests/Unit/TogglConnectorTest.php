<?php

use App\Http\Integrations\Toggl\Requests\CreateEntryRequest;
use App\Http\Integrations\Toggl\Requests\ProjectsRequest;
use App\Http\Integrations\Toggl\Requests\TagsRequest;
use App\Http\Integrations\Toggl\Requests\TasksRequest;
use App\Http\Integrations\Toggl\Requests\UpdateEntryRequest;
use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use App\Task;
use App\TimeEntry;
use App\Token;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('resolves base url correctly', function () {
    Token::factory()->create();
    $connector = new TogglConnector;

    expect($connector->resolveBaseUrl())->toBe('https://api.track.toggl.com/api/v9');
});

it('accepts a token string directly', function () {
    $connector = new TogglConnector('test-token-string');

    expect($connector->resolveBaseUrl())->toBe('https://api.track.toggl.com/api/v9');
});

it('loads token from database when no string provided', function () {
    Token::factory()->create([
        'name' => 'default',
        'contents' => 'db-token',
        'default_workspace_id' => '999',
    ]);

    $connector = new TogglConnector;

    expect($connector->resolveBaseUrl())->toBe('https://api.track.toggl.com/api/v9');
});

it('throws when no token exists in database', function () {
    new TogglConnector;
})->throws(ModelNotFoundException::class);

it('sends projects request', function () {
    Token::factory()->create(['default_workspace_id' => '999']);
    $connector = new TogglConnector;

    $mockClient = new MockClient([
        ProjectsRequest::class => MockResponse::make([
            ['id' => 1, 'name' => 'Project A'],
        ]),
    ]);

    $connector->withMockClient($mockClient);
    $response = $connector->projects();

    expect($response->json())->toHaveCount(1)
        ->and($response->json()[0]['name'])->toBe('Project A');
});

it('sends tags request', function () {
    Token::factory()->create(['default_workspace_id' => '999']);
    $connector = new TogglConnector;

    $mockClient = new MockClient([
        TagsRequest::class => MockResponse::make([
            ['id' => 1, 'name' => 'bug'],
            ['id' => 2, 'name' => 'feature'],
        ]),
    ]);

    $connector->withMockClient($mockClient);
    $response = $connector->tags();

    expect($response->json())->toHaveCount(2);
});

it('sends tasks request with project', function () {
    Token::factory()->create(['default_workspace_id' => '999']);
    $connector = new TogglConnector;

    $project = Project::factory()->create(['ext_id' => 555]);

    $mockClient = new MockClient([
        TasksRequest::class => MockResponse::make([
            ['id' => 10, 'name' => 'Task A'],
        ]),
    ]);

    $connector->withMockClient($mockClient);
    $response = $connector->tasks($project);

    expect($response->json())->toHaveCount(1);
});

it('sends create entry request', function () {
    Token::factory()->create(['default_workspace_id' => '999']);
    $connector = new TogglConnector;

    $task = Task::factory()->create();
    $entry = TimeEntry::factory()->forTask($task)->create();

    $mockClient = new MockClient([
        CreateEntryRequest::class => MockResponse::make(['id' => 77777]),
    ]);

    $connector->withMockClient($mockClient);
    $response = $connector->createEntry($entry);

    expect($response->json('id'))->toBe(77777);
});

it('sends update entry request', function () {
    Token::factory()->create(['default_workspace_id' => '999']);
    $connector = new TogglConnector;

    $task = Task::factory()->create();
    $entry = TimeEntry::factory()->forTask($task)->pushed(88888)->create();

    $mockClient = new MockClient([
        UpdateEntryRequest::class => MockResponse::make(['id' => 88888]),
    ]);

    $connector->withMockClient($mockClient);
    $response = $connector->updateEntry($entry);

    expect($response->json('id'))->toBe(88888);
});
