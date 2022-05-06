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
        RecipeType::Create(['name' => 'Entrée']);
        RecipeType::Create(['name' => 'Plat']);
        RecipeType::Create(['name' => 'Dessert']);
        RecipeType::Create(['name' => 'Amuse Gueule']);
        RecipeType::Create(['name' => 'Sauce']);
        RecipeType::Create(['name' => 'Accompagnement']);
        RecipeType::Create(['name' => 'Boisson']);
        RecipeType::Create(['name' => 'Confiserie']);
        RecipeType::Create(['name' => 'Conseil']);
    }
}
