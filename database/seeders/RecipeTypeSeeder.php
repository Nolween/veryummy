<?php

namespace Database\Seeders;

use App\Models\RecipeType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Création des principaux types de plats
        RecipeType::create(['name' => 'Entrée']);
        RecipeType::create(['name' => 'Plat']);
        RecipeType::create(['name' => 'Dessert']);
        RecipeType::create(['name' => 'Amuse Gueule']);
        RecipeType::create(['name' => 'Sauce']);
        RecipeType::create(['name' => 'Accompagnement']);
        RecipeType::create(['name' => 'Boisson']);
        RecipeType::create(['name' => 'Confiserie']);
        RecipeType::create(['name' => 'Conseil']);
    }
}
