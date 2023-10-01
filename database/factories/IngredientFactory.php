<?php

namespace Database\Factories;

use App\Enums\Diets;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Find an icon name in the public/icons folder
        $icons = scandir(public_path('svg/ingredients'));
        $icon = $icons[array_rand($icons)];

        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $created_at = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'name'        => fake()->word(),
            'icon'        => $icon,
            'user_id'     => $user->id,
            'is_accepted' => fake()->boolean(90),
            'diets'       => fake()->randomElements(
                Diets::allValues(),
                fake()->numberBetween(0, count(Diets::allValues()))
            ),
            'created_at'  => $created_at,
            'updated_at'  => fake()->dateTimeBetween($created_at, 'now'),
        ];
    }
}
