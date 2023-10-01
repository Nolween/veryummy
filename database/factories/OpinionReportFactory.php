<?php

namespace Database\Factories;

use App\Models\RecipeOpinion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpinionReport>
 */
class OpinionReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $opinion = RecipeOpinion::inRandomOrder()->first() ?? RecipeOpinion::factory()->create();

        $created_at = fake()->dateTimeBetween('-1 year', 'now');


        return [
            'user_id'    => $user->id,
            'opinion_id' => $opinion->id,
            'created_at' => $created_at,
            'updated_at' => fake()->dateTimeBetween($created_at, 'now'),
        ];
    }
}
