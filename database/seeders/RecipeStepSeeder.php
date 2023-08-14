<?php

namespace Database\Seeders;

use App\Models\RecipeStep;
use Illuminate\Database\Seeder;

class RecipeStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RecipeStep::factory(500)->create();
    }
}
