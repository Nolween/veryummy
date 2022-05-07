<?php

namespace Database\Seeders;

use App\Models\RecipeOpinion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeOpinionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RecipeOpinion::factory(100)->create();
    }
}
