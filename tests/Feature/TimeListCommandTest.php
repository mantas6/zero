<?php

use App\Project;
use App\Task;
use App\TimeEntry;

it('lists today time entries', function () {
    $task = Task::factory()->create(['name' => 'Test task']);
    TimeEntry::factory()->forTask($task)->create(['started_at' => now()]);

    $this->artisan('time:list')
        ->expectsOutputToContain('Test task')
        ->assertExitCode(0);
});

it('shows info when no entries exist', function () {
    $this->artisan('time:list')
        ->expectsOutputToContain('No time entries for today')
        ->assertExitCode(0);
});

it('does not show yesterday entries', function () {
    $task = Task::factory()->create(['name' => 'Old task']);
    TimeEntry::factory()->forTask($task)->yesterday()->create();

    $this->artisan('time:list')
        ->expectsOutputToContain('No time entries for today')
        ->assertExitCode(0);
});

it('filters by project when TG_PROJECT_ID is set', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();
    $taskA = Task::factory()->forProject($projectA)->create(['name' => 'Project A task']);
    $taskB = Task::factory()->forProject($projectB)->create(['name' => 'Project B task']);

    TimeEntry::factory()->forTask($taskA)->create(['started_at' => now()]);
    TimeEntry::factory()->forTask($taskB)->create(['started_at' => now()]);

    config(['app.project_id' => $projectA->id]);

    $this->artisan('time:list')
        ->expectsOutputToContain('Project A task')
        ->doesntExpectOutputToContain('Project B task')
        ->assertExitCode(0);
});

it('handles orphaned entries gracefully', function () {
    TimeEntry::factory()->create(['task_id' => 0, 'started_at' => now()]);

    $this->artisan('time:list')
        ->expectsOutputToContain('(unknown task)')
        ->assertExitCode(0);
});
