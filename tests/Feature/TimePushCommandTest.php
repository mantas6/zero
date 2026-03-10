<?php

use App\Http\Integrations\Toggl\Requests\CreateEntryRequest;
use App\Http\Integrations\Toggl\Requests\UpdateEntryRequest;
use App\Task;
use App\TimeEntry;
use App\Token;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(function () {
    MockClient::destroyGlobal();
});

it('fails when not authenticated', function () {
    $this->artisan('time:push')
        ->expectsOutputToContain('Not authenticated')
        ->assertExitCode(1);
});

it('shows info when no completed entries exist', function () {
    Token::factory()->create();

    MockClient::global([]);

    $this->artisan('time:push')
        ->expectsOutputToContain('No completed time entries to push')
        ->assertExitCode(0);
});

it('creates new entries on Toggl', function () {
    Token::factory()->create();

    $task = Task::factory()->create(['name' => 'Push test']);
    $entry = TimeEntry::factory()->forTask($task)->create(['started_at' => now()]);

    MockClient::global([
        CreateEntryRequest::class => MockResponse::make(['id' => 12345]),
    ]);

    $this->artisan('time:push')
        ->expectsOutputToContain('Push test')
        ->expectsOutputToContain('created')
        ->assertExitCode(0);

    $entry->refresh();
    expect($entry->ext_id)->toBe(12345);
});

it('updates existing entries on Toggl', function () {
    Token::factory()->create();

    $task = Task::factory()->create(['name' => 'Update test']);
    TimeEntry::factory()
        ->forTask($task)
        ->pushed(99999)
        ->create(['started_at' => now()]);

    MockClient::global([
        UpdateEntryRequest::class => MockResponse::make(['id' => 99999]),
    ]);

    $this->artisan('time:push')
        ->expectsOutputToContain('Update test')
        ->expectsOutputToContain('updated')
        ->assertExitCode(0);
});

it('does not push running entries', function () {
    Token::factory()->create();

    $task = Task::factory()->create();
    TimeEntry::factory()->forTask($task)->running()->create();

    MockClient::global([]);

    $this->artisan('time:push')
        ->expectsOutputToContain('No completed time entries to push')
        ->assertExitCode(0);
});
