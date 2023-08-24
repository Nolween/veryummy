<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;


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

    //! A vérifier
    // Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(RouteServiceProvider::HOME . '?verified=1');
});

test('email is not verified', function () {
    // Création d'un utilisateur
    $user = User::factory()->create(
        [

            'email_verified_at' => null
        ]
    );;

    // Generate verification URL with an invalid hash
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1('wrong-email')
        ]
    );

    // Act as the user and visit the verification URL
    $this->actingAs($user)->get($verificationUrl);

    // Refresh the user instance and check the verification status
    $user = $user->fresh();

    $this->assertFalse($user->hasVerifiedEmail());
});
