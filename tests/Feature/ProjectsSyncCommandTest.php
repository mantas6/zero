<?php

use App\Http\Integrations\Toggl\Requests\ProjectsRequest;
use App\Project;
use App\Token;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(function () {
    MockClient::destroyGlobal();
});

it('fails when not authenticated', function () {
    $this->artisan('projects:sync')
        ->expectsOutputToContain('Not authenticated')
        ->assertExitCode(1);
});

it('syncs projects from Toggl API', function () {
    Token::factory()->create();

    MockClient::global([
        ProjectsRequest::class => MockResponse::make([
            [
                'id' => 1001,
                'name' => 'New Project',
                'active' => true,
                'billable' => true,
                'color' => '#ff0000',
                'client_name' => 'Test Client',
            ],
        ]),
    ]);

    $this->artisan('projects:sync')
        ->expectsOutputToContain('New Project')
        ->expectsOutputToContain('Synced 1 projects')
        ->assertExitCode(0);

    $project = Project::query()->where('ext_id', 1001)->first();
    expect($project)->not->toBeNull()
        ->and($project->name)->toBe('New Project')
        ->and($project->active)->toBeTrue()
        ->and($project->billable)->toBeTrue()
        ->and($project->color)->toBe('#ff0000')
        ->and($project->client_name)->toBe('Test Client');
});

it('updates existing projects on re-sync', function () {
    Token::factory()->create();
    Project::factory()->create(['ext_id' => 1001, 'name' => 'Old Name']);

    MockClient::global([
        ProjectsRequest::class => MockResponse::make([
            [
                'id' => 1001,
                'name' => 'Updated Name',
                'active' => true,
                'billable' => false,
                'color' => '#00ff00',
                'client_name' => null,
            ],
        ]),
    ]);

    $this->artisan('projects:sync')
        ->expectsOutputToContain('Updated Name')
        ->assertExitCode(0);

    expect(Project::query()->where('ext_id', 1001)->count())->toBe(1);
    expect(Project::query()->where('ext_id', 1001)->first()->name)->toBe('Updated Name');
});

it('warns when no projects found', function () {
    Token::factory()->create();

    MockClient::global([
        ProjectsRequest::class => MockResponse::make([]),
    ]);

    $this->artisan('projects:sync')
        ->expectsOutputToContain('No projects found')
        ->assertExitCode(1);
});

it('filters to active projects only with --active flag', function () {
    Token::factory()->create();

    MockClient::global([
        ProjectsRequest::class => MockResponse::make([
            ['id' => 1, 'name' => 'Active', 'active' => true, 'billable' => false, 'color' => '#000', 'client_name' => null],
            ['id' => 2, 'name' => 'Archived', 'active' => false, 'billable' => false, 'color' => '#000', 'client_name' => null],
        ]),
    ]);

    $this->artisan('projects:sync', ['--active' => true])
        ->expectsOutputToContain('Active')
        ->doesntExpectOutputToContain('Archived')
        ->expectsOutputToContain('Synced 1 projects')
        ->assertExitCode(0);
});
