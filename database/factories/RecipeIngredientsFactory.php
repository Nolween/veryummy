<?php

namespace Database\Factories;

use App\Enums\Units;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeIngredients>
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
            'unit'          => fake()->randomElement(Units::allValues()),
            'ingredient_id' => Ingredient::inRandomOrder()->first()->id,
            'quantity'      => rand(1, 5),
            'order'         => Recipe::where('id', $recipeId)->get()->count() + 1,
        ];
    }
}
