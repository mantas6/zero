<?php

use App\Project;
use App\Task;
use App\TimeEntry;

it('starts a timer for a valid task', function () {
    $task = Task::factory()->create(['name' => 'Build feature']);

    $this->artisan('time:start', ['task-id' => $task->id])
        ->expectsOutputToContain('Timer started for: Build feature')
        ->assertExitCode(0);

    expect(TimeEntry::query()->whereToday()->count())->toBe(1);
});

it('stops a running timer when starting a new one', function () {
    $task1 = Task::factory()->create();
    $task2 = Task::factory()->create();

    TimeEntry::factory()->forTask($task1)->running()->create();

    $this->artisan('time:start', ['task-id' => $task2->id])
        ->assertExitCode(0);

    $entries = TimeEntry::query()->whereToday()->get();
    $stoppedEntries = $entries->whereNotNull('stopped_at');
    $runningEntries = $entries->whereNull('stopped_at');

    expect($stoppedEntries)->toHaveCount(1)
        ->and($runningEntries)->toHaveCount(1);
});

it('fails with non-numeric task id', function () {
    $this->artisan('time:start', ['task-id' => 'abc'])
        ->expectsOutputToContain('Task ID must be a number')
        ->assertExitCode(1);
});

it('fails when task does not exist', function () {
    $this->artisan('time:start', ['task-id' => 99999])
        ->expectsOutputToContain('Task with ID 99999 not found')
        ->assertExitCode(1);
});

it('fails when task does not belong to project filter', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();
    $task = Task::factory()->forProject($projectB)->create(['name' => 'Wrong project task']);

    config(['app.project_id' => $projectA->id]);

    $this->artisan('time:start', ['task-id' => $task->id])
        ->expectsOutputToContain('does not belong to project')
        ->assertExitCode(1);
});
