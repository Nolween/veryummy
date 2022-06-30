<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // Initialisation d'un utilisateur
    public function initialize_user()
    {
        // Création d'un rôle, nécessaire pour la création d'un utilisateur
        Role::create(['name' => 'Administrateur']);
        // Création d'un utilisateur
        $user = User::create(['name' => 'Visiteur', 'email' => 'visiteur.test@test.com', 'password' => bcrypt('123456'), 'role_id' => 1, 'is_banned' => false, 'email_verified_at' => null]);

        return $user;
    }

    public function test_email_verification_screen_can_be_rendered()
    {
        $user = $this->initialize_user();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
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

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::HOME . '?verified=1');
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
