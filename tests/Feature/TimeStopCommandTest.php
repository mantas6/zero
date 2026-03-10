<?php

use App\Task;
use App\TimeEntry;

it('stops a running timer', function () {
    $task = Task::factory()->create();
    TimeEntry::factory()->forTask($task)->running()->create();

    $this->artisan('time:stop')
        ->expectsOutputToContain('Timer stopped')
        ->assertExitCode(0);

    $entry = TimeEntry::query()->first();

    expect($entry->stopped_at)->not->toBeNull();
});

it('warns when no timer is running', function () {
    $this->artisan('time:stop')
        ->expectsOutputToContain('No timer is currently running')
        ->assertExitCode(1);
});

it('does not stop entries from yesterday', function () {
    TimeEntry::factory()->yesterday()->running()->create([
        'started_at' => now()->subDay(),
        'stopped_at' => null,
    ]);

    $this->artisan('time:stop')
        ->expectsOutputToContain('No timer is currently running')
        ->assertExitCode(1);
});
