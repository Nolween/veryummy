<?php

namespace Database\Seeders;

use App\Models\RecipeIngredients;
use Illuminate\Database\Seeder;

class RecipeIngredientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RecipeIngredients::factory(700)->create();
    }
}
