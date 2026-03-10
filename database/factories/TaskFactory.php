<?php

namespace Database\Factories;

use App\Project;
use App\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'ext_id' => $this->faker->unique()->randomNumber(8),
            'active' => true,
            'project_id' => Project::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function forProject(Project $project): static
    {
        return $this->state(['project_id' => $project->id]);
    }
}
