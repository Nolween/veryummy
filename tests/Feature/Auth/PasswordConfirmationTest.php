<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
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
