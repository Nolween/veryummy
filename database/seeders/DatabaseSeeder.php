<?php

namespace Database\Seeders;

use App\Models\Ingredient;
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
        User::create(['name' => 'Cashandrick', 'email' => 'nolween.lopez@gmail.com', 'password' => bcrypt('123456'), 'role_id' => 1, 'is_banned' => false, 'email_verified_at' => now()]);
        // \App\Models\User::factory(10)->create();
        // Création des paramètres
        $this->call(UnitSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RecipeTypeSeeder::class);
        // Création des jeux de test
        User::factory(20)->create();
        $this->call(IngredientSeeder::class);
    }
}
