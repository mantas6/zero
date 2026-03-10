<?php

use App\Project;
use App\Task;
use App\TimeEntry;

it('displays current task name', function () {
    $task = Task::factory()->create(['name' => 'Design UI']);
    TimeEntry::factory()->forTask($task)->running()->create();

    $this->artisan('time:current')
        ->expectsOutput('Design UI')
        ->assertExitCode(0);
});

it('displays project and task name with --with-project flag', function () {
    $project = Project::factory()->create(['name' => 'Acme Corp']);
    $task = Task::factory()->forProject($project)->create(['name' => 'Fix bug']);
    TimeEntry::factory()->forTask($task)->running()->create();

    $this->artisan('time:current', ['--with-project' => true])
        ->expectsOutput('Acme Corp: Fix bug')
        ->assertExitCode(0);
});

it('warns when no timer is running', function () {
    $this->artisan('time:current')
        ->expectsOutputToContain('No timer is currently running')
        ->assertExitCode(1);
});

it('shows unknown task for orphaned entry', function () {
    $entry = TimeEntry::factory()->running()->create(['task_id' => 0]);

    $this->artisan('time:current')
        ->expectsOutput('(unknown task)')
        ->assertExitCode(0);
});
