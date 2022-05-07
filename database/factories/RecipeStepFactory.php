<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recipe;

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
        $recipeId = rand(1, 100);

        return [
            'recipe_id' => $recipeId,
            'order' => Recipe::where('id', $recipeId)->get()->count() + 1,
            'description' => $this->faker->text()
        ];
    }
}
