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
        $recipe = Recipe::inRandomOrder()->first() ?? Recipe::factory()->create();

        $ingredient = Ingredient::inRandomOrder()->first() ?? Ingredient::factory()->create();

        return [
            'recipe_id'     => $recipe->id,
            'unit'          => fake()->randomElement(Units::allValues()),
            'ingredient_id' => $ingredient->id,
            'quantity'      => rand(1, 5),
            'order'         => Recipe::where('id', $recipe->id)->count() + 1,
        ];
    }
}
