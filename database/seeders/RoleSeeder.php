<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $source = [['name' => 'Administrateur'], ['name' => 'Utilisateur']];
        
        foreach ($source as $item) {
            $role = new Role(['name' => $item['name']]);
            $role->timestamps = false;
            $role->save();
        }
    }
}
