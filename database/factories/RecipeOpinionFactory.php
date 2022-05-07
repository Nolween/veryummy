<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeOpinion>
 */
class RecipeOpinionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => rand(1, 21),
            'recipe_id' => rand(1, 100),
            'comment' => $this->faker->paragraph(),
            'score' => rand(1, 5),
            'is_reported' => $this->faker->boolean(10),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
