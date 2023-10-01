<?php

namespace Database\Seeders;

use App\Models\RecipeType;
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

        $source = [
            ['name' => 'Entrée'],
            ['name' => 'Plat'],
            ['name' => 'Dessert'],
            ['name' => 'Amuse Gueule'],
            ['name' => 'Sauce'],
            ['name' => 'Accompagnement'],
            ['name' => 'Boisson'],
            ['name' => 'Confiserie'],
            ['name' => 'Conseil'],
        ];

        foreach ($source as $item) {
            $role             = new RecipeType(['name' => $item['name']]);
            $role->timestamps = false;
            $role->save();
        }

    }
}
