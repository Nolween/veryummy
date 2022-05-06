<?php

namespace Database\Seeders;

use App\Models\Unit;
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
        $source = [
            ['name' => 'Unité'],
            ['name' => 'Gramme'],
            ['name' => 'Kilo'],
            ['name' => 'Cuillère à soupe'],
            ['name' => 'Cuillère à café'],
            ['name' => 'Centilitre'],
            ['name' => 'Litre'],
            ['name' => 'Pincée'],
            ['name' => 'Sachet'],
            ['name' => 'Boîte'],
            ['name' => 'Botte'],
            ['name' => 'Tige'],
            ['name' => 'Grappe'],
            ['name' => 'Gousse'],
            ['name' => 'Tablette'],
        ];
        foreach ($source as $item) {
            $unit = new Unit(['name' => $item['name']]);
            $unit->timestamps = false;
            $unit->save();
        }
    }
}
