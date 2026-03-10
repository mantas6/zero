<?php

use App\Task;
use App\TimeEntry;
use Illuminate\Support\Carbon;

it('displays elapsed time in hours and minutes', function () {
    $task = Task::factory()->create();
    Carbon::setTestNow(Carbon::parse('2026-03-10 12:00:00'));

    TimeEntry::factory()
        ->forTask($task)
        ->running()
        ->startedAt(Carbon::parse('2026-03-10 09:45:00'))
        ->create();

    $this->artisan('time:elapsed')
        ->expectsOutput('2h 15m')
        ->assertExitCode(0);

    Carbon::setTestNow();
});

it('displays elapsed time in minutes only', function () {
    $task = Task::factory()->create();
    Carbon::setTestNow(Carbon::parse('2026-03-10 12:00:00'));

    TimeEntry::factory()
        ->forTask($task)
        ->running()
        ->startedAt(Carbon::parse('2026-03-10 11:30:00'))
        ->create();

    $this->artisan('time:elapsed')
        ->expectsOutput('30m')
        ->assertExitCode(0);

    Carbon::setTestNow();
});

it('displays elapsed time in seconds for short durations', function () {
    $task = Task::factory()->create();
    Carbon::setTestNow(Carbon::parse('2026-03-10 12:00:00'));

    TimeEntry::factory()
        ->forTask($task)
        ->running()
        ->startedAt(Carbon::parse('2026-03-10 11:59:45'))
        ->create();

    $this->artisan('time:elapsed')
        ->expectsOutput('15s')
        ->assertExitCode(0);

    Carbon::setTestNow();
});

it('warns when no timer is running', function () {
    $this->artisan('time:elapsed')
        ->expectsOutputToContain('No timer is currently running')
        ->assertExitCode(1);
});
