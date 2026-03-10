<?php

use App\Project;
use App\Task;

it('has many tasks', function () {
    $project = Project::factory()->create();
    $task = Task::factory()->forProject($project)->create();

    expect($project->tasks)->toHaveCount(1)
        ->and($project->tasks->first()->id)->toBe($task->id);
});

it('casts active to boolean', function () {
    $project = Project::factory()->create(['active' => 1]);

    expect($project->active)->toBeTrue();
});

it('casts billable to boolean', function () {
    $project = Project::factory()->billable()->create();

    expect($project->billable)->toBeTrue();
});

it('casts tags to array', function () {
    $project = Project::factory()->withTags(['bug', 'feature'])->create();

    expect($project->tags)->toBe(['bug', 'feature']);
});

it('scopes to active projects only', function () {
    Project::factory()->create(['active' => true]);
    Project::factory()->create(['active' => false]);

    expect(Project::query()->whereActive()->count())->toBe(1);
});

it('returns empty tags when null', function () {
    $project = Project::factory()->create(['tags' => null]);
    $project->refresh();

    expect($project->tags)->toBeNull();
});

it('stores and retrieves ext_id', function () {
    $project = Project::factory()->create(['ext_id' => 12345678]);

    expect($project->ext_id)->toBe(12345678);
});
