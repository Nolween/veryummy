<?php

use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;

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

test('confirm password screen can be rendered', function () {
    $user = initialize_user();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertStatus(200);
});

test('password can be confirmed', function () {
    $user = initialize_user();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => '123456',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    $user = initialize_user();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
});
