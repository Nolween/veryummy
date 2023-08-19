<?php

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

/**
 * Création d'un utilisateur
 */
function initialize_user(bool $banned = false, bool $admin = false): User
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

test('email verification screen can be rendered', function () {
    $user = initialize_user();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(302);
});

test('email can be verified', function () {
    $user = initialize_user();

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
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(RouteServiceProvider::HOME.'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = initialize_user();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
