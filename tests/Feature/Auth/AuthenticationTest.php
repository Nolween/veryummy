<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // Initialisation d'un utilisateur
    public function initialize_user()
    {
        // Création d'un rôle, nécessaire pour la création d'un utilisateur
        Role::create(['name' => 'Administrateur']);
        // Création d'un utilisateur
        $user = User::create(['name' => 'Visiteur', 'email' => 'visiteur.test@test.com', 'password' => bcrypt('123456'), 'role_id' => 1, 'is_banned' => false, 'email_verified_at' => now()]);

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
