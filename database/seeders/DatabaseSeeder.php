<?php

namespace Database\Seeders;

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
        // Suppression préalable des images de rectettes dans storage
        $files = glob(storage_path('app/public/img/full/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $files = glob(storage_path('app/public/img/thumbnail/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Création des paramètres
        $this->call(RecipeTypeSeeder::class);
        // Création des jeux de test
        $this->call(UserSeeder::class);
        $this->call(IngredientSeeder::class);
        $this->call(RecipeSeeder::class);
        $this->call(RecipeOpinionSeeder::class);
        $this->call(RecipeStepSeeder::class);
        // $this->call(RecipeIngredientsSeeder::class);
        $this->call(OpinionReportSeeder::class);
    }
}
