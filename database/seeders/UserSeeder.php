<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(['name' => 'Cashandrick', 'email' => 'nolween.lopez@gmail.com', 'password' => bcrypt('123456'), 'role_id' => 1, 'is_banned' => false, 'email_verified_at' => now()]);
        User::factory(20)->create();
    }
}
