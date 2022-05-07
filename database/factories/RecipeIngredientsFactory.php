<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recipe;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeIngredients>
 */
class RecipeIngredientsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $recipeId = rand(1, 100);

        return [
            'recipe_id' => $recipeId,
            'unit_id' => rand(1, 18),
            'ingredient_id' => rand(1, 2412),
            'quantity' => rand(1, 5),
            'order' => Recipe::where('id', $recipeId)->get()->count() + 1,
        ];
    }
}
