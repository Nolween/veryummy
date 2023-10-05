<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

it('denies user update if user is banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = actingAs($user)->put(route('my-account.update'), [
        'name'  => 'test',
        'email' => 'test@test.com'
    ]);

    $response->assertStatus(403);
});

it('denies user update if user is not respecting rules', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = actingAs($user)->put(route('my-account.update'), [
        'password'     => 222,
        'confirmation' => 111,
        'email'        => 'test',
        'name'         => 416518,
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['password', 'confirmation', 'email', 'name', 'current-password']);
});

it('denies user update if user give an existing email', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0, 'password' => bcrypt('test')]);
    User::factory()->create(['email' => 'this.already@exists.com']);

    $response = actingAs($user)->put(route('my-account.update'), [
        'password'         => 'test2',
        'confirmation'     => 'test2',
        'current-password' => 'test',
        'name'             => 'test',
        'email'            => 'this.already@exists.com'
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['email']);

});


it('denies user update if user give an existing name', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0, 'password' => bcrypt('test')]);
    User::factory()->create(['name' => 'this.already.exists']);

    $response = actingAs($user)->put(route('my-account.update'), [
        'password'         => 'test2',
        'confirmation'     => 'test2',
        'current-password' => 'test',
        'name'             => 'this.already.exists'
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['name']);

});


it('denies user update if user give a different password and confirmation', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0, 'password' => bcrypt('test')]);

    $response = actingAs($user)->put(route('my-account.update'), [
        'password'         => 'test2',
        'confirmation'     => 'test3',
        'current-password' => 'test',
        'name'             => 'test'
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['password']);

});


it('updates user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0, 'password' => bcrypt('test')]);

    $response = actingAs($user)->put(route('my-account.update'), [
        'password'         => 'test2',
        'confirmation'     => 'test2',
        'current-password' => 'test',
        'name'             => 'test',
        'email'            => 'test@test.com',
    ]);

    $response->assertRedirect(route('home'));
    // On s'attend à avoir un message de succès
    $response->assertSessionHas('userUpdateSuccess');
    // On s'attend à avoir un utilisateur en base de données
    expect(User::where('email', 'test@test.com')->first())->not()->toBeNull();
});
