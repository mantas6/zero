<?php

use App\Http\Integrations\Toggl\Requests\CreateEntryRequest;
use App\Project;
use App\Task;
use App\TimeEntry;
use Illuminate\Support\Carbon;

it('resolves the correct endpoint', function () {
    $entry = TimeEntry::factory()->create();
    $request = new CreateEntryRequest('12345', $entry);

    expect($request->resolveEndpoint())->toBe('/workspaces/12345/time_entries');
});

it('uses POST method', function () {
    $entry = TimeEntry::factory()->create();
    $request = new CreateEntryRequest('12345', $entry);

    expect($request->getMethod()->value)->toBe('POST');
});

it('builds correct body for completed entry', function () {
    $project = Project::factory()->billable()->create(['ext_id' => 100]);
    $task = Task::factory()->forProject($project)->create(['name' => 'Test task', 'ext_id' => 200]);

    $startedAt = Carbon::parse('2026-03-10 09:00:00');
    $stoppedAt = Carbon::parse('2026-03-10 10:30:00');

    $entry = TimeEntry::factory()
        ->forTask($task)
        ->startedAt($startedAt)
        ->stoppedAt($stoppedAt)
        ->create();

    $request = new CreateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['billable'])->toBeTrue()
        ->and($body['created_with'])->toBe('zero-cli')
        ->and($body['description'])->toBe('Test task')
        ->and($body['duration'])->toBe(5400)
        ->and($body['project_id'])->toBe(100)
        ->and($body['task_id'])->toBe(200)
        ->and($body['workspace_id'])->toBe(12345);
});

it('uses duration -1 for running entry', function () {
    $task = Task::factory()->create();
    $entry = TimeEntry::factory()
        ->forTask($task)
        ->running()
        ->create();

    $request = new CreateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['duration'])->toBe(-1)
        ->and($body['stop'])->toBeNull();
});

it('includes tags from project when present', function () {
    $project = Project::factory()->withTags(['bug', 'urgent'])->create();
    $task = Task::factory()->forProject($project)->create();
    $entry = TimeEntry::factory()->forTask($task)->create();

    $request = new CreateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['tags'])->toBe(['bug', 'urgent']);
});

it('excludes tags key when project has no tags', function () {
    $project = Project::factory()->create(['tags' => []]);
    $task = Task::factory()->forProject($project)->create();
    $entry = TimeEntry::factory()->forTask($task)->create();

    $request = new CreateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body)->not->toHaveKey('tags');
});

it('defaults billable to true when no project', function () {
    $task = Task::factory()->create();
    // Detach the project relationship by setting project_id to a non-existent ID
    $entry = TimeEntry::factory()->create(['task_id' => $task->id]);

    // Simulate an orphaned task with no project
    $task->update(['project_id' => 0]);
    $entry->refresh();
    $entry->load('task.project');

    $request = new CreateEntryRequest('12345', $entry);
    $body = $request->body()->all();

    expect($body['billable'])->toBeTrue();
});
