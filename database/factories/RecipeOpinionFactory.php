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
        $isFavorite = $this->faker->boolean(30);
        return [
            'user_id' => rand(1, 21),
            'recipe_id' => rand(1, 100),
            'comment' => $this->faker->paragraph(),
            'score' => rand(1, 5),
            'is_favorite' => $isFavorite,
            'is_reported' => $isFavorite == true ? false : $this->faker->boolean(30),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
