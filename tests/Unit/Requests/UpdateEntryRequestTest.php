<?php

use App\Http\Integrations\Toggl\Requests\UpdateEntryRequest;
use App\Project;
use App\Task;
use App\TimeEntry;
use Illuminate\Support\Carbon;

it('resolves the correct endpoint with entry ext_id', function () {
    $entry = TimeEntry::factory()->pushed(99999)->create();
    $request = new UpdateEntryRequest('12345', $entry);

    expect($request->resolveEndpoint())->toBe('/workspaces/12345/time_entries/99999');
});

it('uses PUT method', function () {
    $entry = TimeEntry::factory()->pushed()->create();
    $request = new UpdateEntryRequest('12345', $entry);

    expect($request->getMethod()->value)->toBe('PUT');
});

it('builds correct body for completed entry', function () {
    $project = Project::factory()->create(['ext_id' => 100, 'billable' => false]);
    $task = Task::factory()->forProject($project)->create(['name' => 'Updated task', 'ext_id' => 200]);

    $startedAt = Carbon::parse('2026-03-10 09:00:00');
    $stoppedAt = Carbon::parse('2026-03-10 11:00:00');

    $entry = TimeEntry::factory()
        ->forTask($task)
        ->pushed(55555)
        ->startedAt($startedAt)
        ->stoppedAt($stoppedAt)
        ->create();

    $request = new UpdateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['billable'])->toBeFalse()
        ->and($body['description'])->toBe('Updated task')
        ->and($body['duration'])->toBe(7200)
        ->and($body['project_id'])->toBe(100)
        ->and($body['task_id'])->toBe(200)
        ->and($body['workspace_id'])->toBe(12345);
});

it('includes tags from project when present', function () {
    $project = Project::factory()->withTags(['review'])->create();
    $task = Task::factory()->forProject($project)->create();
    $entry = TimeEntry::factory()->forTask($task)->pushed()->create();

    $request = new UpdateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['tags'])->toBe(['review']);
});
