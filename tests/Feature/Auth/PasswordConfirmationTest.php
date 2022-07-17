<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class PasswordConfirmationTest extends TestCase
{

    /**
     * Création d'un utilisateur
     *
     * @param boolean $banned
     * @param boolean $admin
     * @return User
     */
    private function initialize_user(bool $banned = false, bool $admin = false): User
    {
        $faker = Faker::create();
        $newName = $faker->firstName() . ' ' . $faker->lastName();
        $mail = $faker->email();
        if ($admin == true) {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Administrateur')->first();
            if (!$role) {
                $role = Role::factory()->create(['name' => 'Administrateur']);
            }
        } else {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Utilisateur')->first();
            if (!$role) {
                $role = Role::factory()->create(['name' => 'Utilisateur']);
            }
        }
        // Création d'un utilisateur
        $user = User::factory()->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => $role->id, 'is_banned' => $banned, 'email_verified_at' => now()]);

        return $user;
    }


    public function test_confirm_password_screen_can_be_rendered()
    {
        $user = $this->initialize_user();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed()
    {
        $user = $this->initialize_user();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password()
    {
        $user = $this->initialize_user();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
