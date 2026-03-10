<?php

use App\Project;
use App\Task;
use App\TimeEntry;
use Illuminate\Support\Carbon;

it('belongs to a task', function () {
    $task = Task::factory()->create();
    $entry = TimeEntry::factory()->forTask($task)->create();

    expect($entry->task->id)->toBe($task->id);
});

it('casts started_at to datetime', function () {
    $entry = TimeEntry::factory()->create();

    expect($entry->started_at)->toBeInstanceOf(Carbon::class);
});

it('casts stopped_at to datetime', function () {
    $entry = TimeEntry::factory()->create();

    expect($entry->stopped_at)->toBeInstanceOf(Carbon::class);
});

it('allows null stopped_at for running entries', function () {
    $entry = TimeEntry::factory()->running()->create();

    expect($entry->stopped_at)->toBeNull();
});

it('scopes to today entries only', function () {
    TimeEntry::factory()->create(['started_at' => now()]);
    TimeEntry::factory()->yesterday()->create();

    expect(TimeEntry::query()->whereToday()->count())->toBe(1);
});

it('scopes to entries for a specific project', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();
    $taskA = Task::factory()->forProject($projectA)->create();
    $taskB = Task::factory()->forProject($projectB)->create();

    TimeEntry::factory()->forTask($taskA)->create();
    TimeEntry::factory()->forTask($taskB)->create();

    expect(TimeEntry::query()->forProject($projectA)->count())->toBe(1)
        ->and(TimeEntry::query()->forProject($projectB)->count())->toBe(1);
});

it('stores ext_id for pushed entries', function () {
    $entry = TimeEntry::factory()->pushed(12345)->create();

    expect($entry->ext_id)->toBe(12345);
});

it('has null ext_id for unpushed entries', function () {
    $entry = TimeEntry::factory()->create();

    expect($entry->ext_id)->toBeNull();
});

it('can have a null task relationship', function () {
    $entry = TimeEntry::factory()->create(['task_id' => 0]);

    expect($entry->task)->toBeNull();
});
