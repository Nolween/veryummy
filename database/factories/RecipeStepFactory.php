<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeStep>
 */
class RecipeStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $recipeId = Recipe::inRandomOrder()->first()->id;

        return [
            'recipe_id' => $recipeId,
            'order' => Recipe::where('id', $recipeId)->get()->count() + 1,
            'description' => fake()->text(),
        ];
    }
}
