<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
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
        $isFavorite = fake()->boolean(30);

        return [
            'user_id'     => User::inRandomOrder()->first()->id,
            'recipe_id'   => Recipe::inRandomOrder()->first()->id,
            'comment'     => $this->faker->paragraph(),
            'score'       => rand(1, 5),
            'is_favorite' => $isFavorite,
            'is_reported' => $isFavorite == true ? false : fake()->boolean(30),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
