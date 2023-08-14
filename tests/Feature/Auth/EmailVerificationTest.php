<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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

    public function test_email_verification_screen_can_be_rendered()
    {
        $user = $this->initialize_user();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(302);
    }

    public function test_email_can_be_verified()
    {

        $user = $this->initialize_user();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        dump($response);

        //! A vérifier
        // Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::HOME.'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = $this->initialize_user();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
