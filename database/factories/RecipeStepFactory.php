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
        $recipe = Recipe::inRandomOrder()->first() ?? Recipe::factory()->create();

        return [
            'recipe_id'   => $recipe->id,
            'order'       => Recipe::where('id', $recipe->id)->get()->count() + 1,
            'description' => fake()->text(),
        ];
    }
}
