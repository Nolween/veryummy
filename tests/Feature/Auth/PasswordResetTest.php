<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
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

    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = $this->initialize_user();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered()
    {
        Notification::fake();

        $user = $this->initialize_user();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        Notification::fake();

        $user = $this->initialize_user();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
