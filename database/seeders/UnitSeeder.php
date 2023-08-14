<?php

namespace Database\Seeders;

use App\Models\Unit;
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
            ['name' => 'Unité(s)'],
            ['name' => 'Gramme(s)'],
            ['name' => 'Kilo(s)'],
            ['name' => 'Cuillère(s) à soupe'],
            ['name' => 'Cuillère(s) à café'],
            ['name' => 'Centilitre(s)'],
            ['name' => 'Litre(s)'],
            ['name' => 'Pincée(s)'],
            ['name' => 'Sachet(s)'],
            ['name' => 'Boîte(s)'],
            ['name' => 'Tige(s)'],
            ['name' => 'Grappe(s)'],
            ['name' => 'Gousse(s)'],
            ['name' => 'Tranche(s)'],
            ['name' => 'Filet(s)'],
            ['name' => 'Verre(s)'],
            ['name' => 'Tablette(s)'],
            ['name' => 'Epi(s)'],
        ];
        foreach ($source as $item) {
            $unit = new Unit(['name' => $item['name']]);
            $unit->timestamps = false;
            $unit->save();
        }
    }
}
