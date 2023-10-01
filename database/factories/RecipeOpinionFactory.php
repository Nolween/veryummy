<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeOpinion>
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

        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $recipe = Recipe::inRandomOrder()->first() ?? Recipe::factory()->create();

        return [
            'user_id'     => $user->id,
            'recipe_id'   => $recipe->id,
            'comment'     => $this->faker->paragraph(),
            'score'       => rand(1, 5),
            'is_favorite' => $isFavorite,
            'is_reported' => $isFavorite == true ? false : fake()->boolean(30),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
