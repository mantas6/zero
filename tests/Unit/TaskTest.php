<?php

use App\Project;
use App\Task;

it('belongs to a project', function () {
    $project = Project::factory()->create();
    $task = Task::factory()->forProject($project)->create();

    expect($task->project->id)->toBe($project->id);
});

it('casts active to boolean', function () {
    $task = Task::factory()->create(['active' => 1]);

    expect($task->active)->toBeTrue();
});

it('scopes to active tasks only', function () {
    $project = Project::factory()->create();
    Task::factory()->forProject($project)->create(['active' => true]);
    Task::factory()->forProject($project)->create(['active' => false]);

    expect(Task::query()->whereActive()->count())->toBe(1);
});

it('stores fillable attributes', function () {
    $task = Task::factory()->create([
        'name' => 'Implement feature',
        'ext_id' => 99999,
    ]);

    expect($task->name)->toBe('Implement feature')
        ->and($task->ext_id)->toBe(99999);
});
