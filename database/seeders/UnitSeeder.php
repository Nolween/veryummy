<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Création des unités de mesure principales
        Unit::create(['name' => 'Unité']);
        Unit::create(['name' => 'Gramme']);
        Unit::create(['name' => 'Kilo']);
        Unit::create(['name' => 'Cuillère à soupe']);
        Unit::create(['name' => 'Cuillère à café']);
        Unit::create(['name' => 'Centilitre']);
        Unit::create(['name' => 'Litre']);
        Unit::create(['name' => 'Pincée']);
        Unit::create(['name' => 'Sachet']);
        Unit::create(['name' => 'Boîte']);
        Unit::create(['name' => 'Botte']);
        Unit::create(['name' => 'Tige']);
        Unit::create(['name' => 'Grappe']);
        Unit::create(['name' => 'Gousse']);
        Unit::create(['name' => 'Tablette']);
    }
}
