<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Unit;
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

        $recipeId = Recipe::inRandomOrder()->first()->id;

        return [
            'recipe_id'     => $recipeId,
            'unit_id'       => Unit::inRandomOrder()->first()->id,
            'ingredient_id' => Ingredient::inRandomOrder()->first()->id,
            'quantity'      => rand(1, 5),
            'order'         => Recipe::where('id', $recipeId)->get()->count() + 1,
        ];
    }
}
