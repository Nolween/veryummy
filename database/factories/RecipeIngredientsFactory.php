<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'unit_id' => rand(1, 17),
            'ingredient_id' => rand(1, 2280),
            'quantity' => rand(1, 5),
            'order' => Recipe::where('id', $recipeId)->get()->count() + 1,
        ];
    }
}
