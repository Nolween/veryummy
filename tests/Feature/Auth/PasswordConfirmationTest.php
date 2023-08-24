<?php

use App\Models\User;
use Faker\Factory as Faker;


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
