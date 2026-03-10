<?php

namespace Database\Factories;

use App\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'ext_id' => $this->faker->unique()->randomNumber(8),
            'active' => true,
            'billable' => false,
            'color' => $this->faker->hexColor(),
            'client_name' => $this->faker->company(),
            'tags' => [],
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function billable(): static
    {
        return $this->state(['billable' => true]);
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function withTags(array $tags): static
    {
        return $this->state(['tags' => $tags]);
    }
}
