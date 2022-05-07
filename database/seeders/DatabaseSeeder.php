<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\OpinionReport;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Création des paramètres
        $this->call(UnitSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RecipeTypeSeeder::class);
        // Création des jeux de test
        $this->call(UserSeeder::class);
        $this->call(IngredientSeeder::class);
        $this->call(RecipeSeeder::class);
        $this->call(RecipeOpinionSeeder::class);
        $this->call(RecipeStepSeeder::class);
        $this->call(RecipeIngredientsSeeder::class);
        $this->call(OpinionReportSeeder::class);
    }
}
