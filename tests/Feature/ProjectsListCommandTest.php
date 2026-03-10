<?php

use App\Project;

it('lists active projects', function () {
    Project::factory()->create(['name' => 'Active Project', 'active' => true]);
    Project::factory()->create(['name' => 'Archived Project', 'active' => false]);

    $this->artisan('projects:list')
        ->expectsOutputToContain('Active Project')
        ->doesntExpectOutputToContain('Archived Project')
        ->assertExitCode(0);
});

it('lists all projects with --all flag', function () {
    Project::factory()->create(['name' => 'Active Project', 'active' => true]);
    Project::factory()->create(['name' => 'Archived Project', 'active' => false]);

    $this->artisan('projects:list', ['--all' => true])
        ->expectsOutputToContain('Active Project')
        ->expectsOutputToContain('Archived Project')
        ->assertExitCode(0);
});

it('shows warning when no projects exist', function () {
    $this->artisan('projects:list')
        ->expectsOutputToContain('No local projects found')
        ->assertExitCode(0);
});

it('displays client name', function () {
    Project::factory()->create([
        'name' => 'Client Project',
        'client_name' => 'Big Corp',
        'active' => true,
    ]);

    $this->artisan('projects:list')
        ->expectsOutputToContain('Big Corp')
        ->assertExitCode(0);
});
