<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{

    private static $order = 1;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => rand(1, 21),
            'recipe_type_id' => rand(1, 9),
            'name' => $this->faker->sentence(),
            'image' => self::$order++ . '.avif',
            'making_time' => rand(1, 100),
            'cooking_time' => rand(1, 180),
            'score' => $this->faker->randomFloat(2, 1, 5),
            'is_accepted' => $this->faker->boolean(90),
            'vegan_compatible' => $this->faker->boolean(60),
            'vegetarian_compatible' => $this->faker->boolean(80),
            'gluten_free_compatible' => $this->faker->boolean(90),
            'halal_compatible' => $this->faker->boolean(80),
            'kosher_compatible' => $this->faker->boolean(60),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
