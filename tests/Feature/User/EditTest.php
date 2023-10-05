<?php

use App\Models\User;

it('it denies user who is not authentificated', function () {
    $response = $this->get(route('my-account.edit'));

    $response->assertStatus(403);
});

it('it shows user account page', function () {
    $user = User::factory()->create(
        ['role' => User::ROLE_USER, 'is_banned' => 0, 'name' => 'test', 'email' => 'test@test.com']
    );

    $response = $this->actingAs($user)->get(route('my-account.edit'))->assertOk();

    $response->assertViewIs('myaccount')->assertViewHas('informations');
    $response->assertViewHas('informations', function ($informations) {
        return $informations->name === 'test' && $informations->email === 'test@test.com';
    });
});
