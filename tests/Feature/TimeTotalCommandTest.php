<?php

use App\Project;
use App\Task;
use App\TimeEntry;
use Illuminate\Support\Carbon;

it('shows total tracked time for today', function () {
    $task = Task::factory()->create();
    Carbon::setTestNow(Carbon::parse('2026-03-10 17:00:00'));

    // 2 hours entry
    TimeEntry::factory()->forTask($task)
        ->startedAt(Carbon::parse('2026-03-10 09:00:00'))
        ->stoppedAt(Carbon::parse('2026-03-10 11:00:00'))
        ->create();

    // 1 hour entry
    TimeEntry::factory()->forTask($task)
        ->startedAt(Carbon::parse('2026-03-10 13:00:00'))
        ->stoppedAt(Carbon::parse('2026-03-10 14:00:00'))
        ->create();

    $this->artisan('time:total')
        ->expectsOutput('3h 0m')
        ->assertExitCode(0);

    Carbon::setTestNow();
});

it('includes running timer in total', function () {
    $task = Task::factory()->create();
    Carbon::setTestNow(Carbon::parse('2026-03-10 10:30:00'));

    // Running entry started 1.5 hours ago
    TimeEntry::factory()->forTask($task)
        ->startedAt(Carbon::parse('2026-03-10 09:00:00'))
        ->stoppedAt(null)
        ->create();

    $this->artisan('time:total')
        ->expectsOutput('1h 30m')
        ->assertExitCode(0);

    Carbon::setTestNow();
});

it('shows info when no entries exist today', function () {
    $this->artisan('time:total')
        ->expectsOutputToContain('No time entries for today')
        ->assertExitCode(0);
});

it('filters by project when TG_PROJECT_ID is set', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();
    $taskA = Task::factory()->forProject($projectA)->create();
    $taskB = Task::factory()->forProject($projectB)->create();

    Carbon::setTestNow(Carbon::parse('2026-03-10 12:00:00'));

    // 1 hour for project A
    TimeEntry::factory()->forTask($taskA)
        ->startedAt(Carbon::parse('2026-03-10 09:00:00'))
        ->stoppedAt(Carbon::parse('2026-03-10 10:00:00'))
        ->create();

    // 2 hours for project B
    TimeEntry::factory()->forTask($taskB)
        ->startedAt(Carbon::parse('2026-03-10 09:00:00'))
        ->stoppedAt(Carbon::parse('2026-03-10 11:00:00'))
        ->create();

    config(['app.project_id' => $projectA->id]);

    $this->artisan('time:total')
        ->expectsOutput('1h 0m')
        ->assertExitCode(0);

    Carbon::setTestNow();
});
