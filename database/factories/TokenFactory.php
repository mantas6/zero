<?php

namespace Database\Factories;

use App\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Token>
 */
class TokenFactory extends Factory
{
    protected $model = Token::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'default',
            'contents' => $this->faker->sha256(),
            'default_workspace_id' => (string) $this->faker->randomNumber(7),
        ];
    }
}
