<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Création d'un utilisateur
     */
    private function initialize_user(bool $banned = false, bool $admin = false): User
    {
        $faker = Faker::create();
        $newName = $faker->firstName().' '.$faker->lastName();
        $mail = $faker->email();
        if ($admin == true) {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Administrateur')->first();
            if (! $role) {
                $role = Role::factory()->create(['name' => 'Administrateur']);
            }
        } else {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Utilisateur')->first();
            if (! $role) {
                $role = Role::factory()->create(['name' => 'Utilisateur']);
            }
        }
        // Création d'un utilisateur
        $user = User::factory()->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => $role->id, 'is_banned' => $banned, 'email_verified_at' => now()]);

        return $user;
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = $this->initialize_user();
        // Accès à la connexion avec les infos d'authentification
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);
        // Test de l'authentification
        $this->assertAuthenticated();
        // Test de redirection vers la vue définie lorsque la connexion est réussie, ici /my-notebook
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = $this->initialize_user();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
