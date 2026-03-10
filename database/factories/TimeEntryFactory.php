<?php

namespace Database\Factories;

use App\Task;
use App\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = Carbon::today()->addHours($this->faker->numberBetween(8, 16));

        return [
            'started_at' => $startedAt,
            'stopped_at' => $startedAt->copy()->addMinutes($this->faker->numberBetween(15, 120)),
            'ext_id' => null,
            'task_id' => Task::factory(),
        ];
    }

    public function running(): static
    {
        return $this->state([
            'started_at' => now()->subMinutes($this->faker->numberBetween(5, 60)),
            'stopped_at' => null,
        ]);
    }

    public function pushed(?int $extId = null): static
    {
        return $this->state([
            'ext_id' => $extId ?? $this->faker->unique()->randomNumber(8),
        ]);
    }

    public function forTask(Task $task): static
    {
        return $this->state(['task_id' => $task->id]);
    }

    public function startedAt(Carbon $time): static
    {
        return $this->state(['started_at' => $time]);
    }

    public function stoppedAt(?Carbon $time): static
    {
        return $this->state(['stopped_at' => $time]);
    }

    public function yesterday(): static
    {
        $startedAt = Carbon::yesterday()->addHours($this->faker->numberBetween(8, 16));

        return $this->state([
            'started_at' => $startedAt,
            'stopped_at' => $startedAt->copy()->addHours(1),
        ]);
    }
}
